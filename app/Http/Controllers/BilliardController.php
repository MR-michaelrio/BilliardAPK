<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billiard;
use App\Models\Meja;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\HargaRental;
use App\Models\Paket;
use App\Models\Produk;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;

class BilliardController extends Controller
{
    //
    public function meja()
    {
        $meja = Meja::all();
        $rental = Billiard::all();
    
        $meja_rental = $meja->map(function($m) use ($rental) {
            $invoice = $rental->firstWhere('no_meja', $m->nomor);
            return [
                'nomor_meja' => $m->nomor,
                'waktu_mulai'=> $invoice && $invoice->waktu_mulai ? $invoice->waktu_mulai->format('Y-m-d H:i:s') : null,
                'waktu_akhir' => $invoice && $invoice->waktu_akhir ? $invoice->waktu_akhir->format('Y-m-d H:i:s') : null,
                'status' => $invoice ? $invoice->status : null // Tambahkan status
            ];
        });
    
        return view('billiard', compact('meja_rental'));
    }

    public function list($no_meja)
    {
        $meja_rental = Billiard::where('no_meja', $no_meja)->first();
        $meja_rental2 = Billiard::where('no_meja', $no_meja)->get();
        $rental = Billiard::where('no_meja', $no_meja)->count();
        
        if ($meja_rental) {
            $makanan = Order::where('id_table', $meja_rental->id_player)
                            ->where('status', 'belum')
                            ->with('items')->get();

            $idplayer = substr($meja_rental->id_player, 0, 1);

            if ($idplayer == 'M') {
                $mejatotal = 0;
                $lama_waktu = '00:00:00';
            } else {
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                if($meja_rental->status == "lanjut"){
                    $lama_waktu = request()->query('lama_main', '00:00:00'); // Get 'lama_main' from URL
                }else{
                    $lama_waktu = $meja_rental->lama_waktu;
                }
                

                // No need to calculate elapsed time since we directly use 'lama_main'
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                // Initialize default per-minute pricing
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;
                
                if (in_array($no_meja, [1, 2])) {
                    // Harga khusus meja 1 dan 2 (Rp 60.000 per jam)
                    $mejatotal = ($total_minutes / 60) * 50000;
                } else {
                    // Hitung harga berdasarkan per menit atau paket
                    $mejatotal = $total_minutes * $harga_per_menit;
            
                    // Iterasi melalui paket untuk mendapatkan harga terbaik
                    $paket = Paket::orderBy('jam', 'asc')->get();
                    $best_price = null; // Default to calculated per-minute price
                    foreach ($paket as $p) {
                        if ($lama_waktu == $p->jam) {
                            $best_price = $p->harga;
                            break;
                        }
                    }
                    $mejatotal = $best_price !== null ? $best_price : $mejatotal;
                }
            }

            // Total biaya keseluruhan
            // Calculate the total for all food items
            $total_makanan = $makanan->flatMap(function($order) {
                return $order->items;
            })->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // Total biaya keseluruhan
            $total = $mejatotal + $total_makanan;
            $total = round($total);
            return view('list', compact('meja_rental', 'meja_rental2', 'no_meja', 'rental', 'makanan', 'total', 'lama_waktu', 'mejatotal'));
        }
        
        return abort(404);
    }

    public function belanja()
    {
        //
        $products = Produk::where('qty', '>', 0)->get();
        $rental = Billiard::all();
        return view('belanja', compact('products','rental'));
    }

    public function belanjastore(Request $request)
    {
        // Validasi data
        $request->validate([
            'id_table' => 'integer',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.note' => 'nullable|string'
        ]);
        $nomor_meja = Billiard::where("id_player",$request->id_table)->first();
        Log::info('Nomor meja12', ['response' => $nomor_meja]);

        // Buat order di database
        $order = Order::create([
            'id_table' => $request->id_table,
            'status' => "belum"
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'metode' => "simpan",
                'note' => $item['note']
            ]);
        }

        // Kirim ke printer dapur via HTTP POST
        try {
            $printerResponse = Http::timeout(5)
        ->withHeaders([
            'x-api-key' => 'secret123'  // Tambahkan header ini!
        ])
        ->post('http://127.0.0.1:6000/print', [
            'meja' => $nomor_meja->no_meja,
            'items' => collect($request->items)->map(function ($item) {
                return [
                    'nama' => $item['name'],
                    'qty' => $item['quantity'],
                    'note' => $item['note']
                ];
            })->toArray()
        ]);


            if (!$printerResponse->successful()) {
                \Log::error('Gagal kirim ke printer', ['response' => $printerResponse->body()]);
            }
        } catch (\Exception $e) {
            \Log::error('Error kirim ke printer: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
