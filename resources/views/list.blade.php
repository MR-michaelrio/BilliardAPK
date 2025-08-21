@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <!-- Main content -->
        <div class="invoice p-3 mb-3">
            <!-- title row -->
            <div class="row">
                <div class="col-12">
                    <h4>
                        Billiard.
                        <small class="float-right">Date: {{ now()->format('d-m-Y') }}</small>
                    </h4>
                </div>
                <!-- /.col -->
            </div>

            <!-- Table row -->
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>QTY</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($meja_rental2 as $r)
                            <tr>
                                <td>1</td>
                                <td>Meja Billiard</td>
                                <td><span id="lama_waktu">{{$lama_waktu}}</span></td>
                            </tr>
                        @endforeach
                        @php 
                            $no = 2;
                        @endphp 
                        @foreach($makanan as $order)
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.invoice -->
    </div>
</div>

<script>

function resetStopwatch(noMeja) {
    const stopwatchKey = `stopwatch_${noMeja}`;
    localStorage.removeItem(stopwatchKey);

    const element = document.querySelector(`.meja[data-nomor-meja="${noMeja}"]`);
    if (element) {
        const stopwatchElement = element.closest('.card-body').querySelector('.stopwatch');
        if (stopwatchElement) {
            stopwatchElement.innerHTML = '00:00:00';
        }
        element.classList.remove('meja-yellow', 'meja-red');
        element.classList.add('meja-green');
    }
}
</script>
@endsection
