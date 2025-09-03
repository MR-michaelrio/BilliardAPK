{{-- resources/views/billiard.blade.php --}}
@extends('layout.main')
@section('content')
<style>
.meja { color: black; width:100%; height:100px; display:flex; align-items:center; justify-content:center; text-align:center; font-size:24px; font-weight:bold; }
.meja-green { background-color:#72fc89; }
.meja-yellow { background-color:#ffd666; }
.meja-red { background-color:#ff6666; }
.countdown, .stopwatch { font-weight:bold; color:red; text-align:center; font-size:24px; }
.card { margin-bottom:20px; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="row">
                @for ($i = 0; $i < 12; $i++)
                    <div class="col-2 col-lg-3">
                        @foreach($meja_rental as $index => $mi)
                            @if($index == $i)
                                <div class="card">
                                    <a href="#" class="menu-link" data-nomor-meja="{{ $mi['nomor_meja'] }}" data-status="{{ $mi['status'] }}">
                                        <div class="card-body">
                                            @php
                                                $activeStatuses = ['baru','lanjut','tambah','tambahlanjut','tambahan'];
                                                $mejaClass = in_array($mi['status'], $activeStatuses) ? 'meja-yellow' : ($mi['status'] === 'selesai' ? 'meja-red' : 'meja-green');
                                                $timerClass = ($mi['status'] === 'lanjut' || $mi['status'] === 'tambahlanjut') ? 'stopwatch' : 'countdown';
                                                $timerText = '00:00:00';
                                            @endphp

                                            <div class="meja {{ $mejaClass }}" 
                                                 data-end-time="{{ $mi['waktu_akhir'] }}" 
                                                 data-start-time="{{ $mi['waktu_mulai'] }}" 
                                                 data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                                Meja {{ $mi['nomor_meja'] }}
                                            </div>

                                            <div class="{{ $timerClass }}" data-status="{{ $mi['status'] }}">
                                                {{ $timerText }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const activeStatuses = ['baru','lanjut','tambah','tambahlanjut','tambahan'];

    function formatTime(ms){
        const h = Math.floor((ms/(1000*60*60))%24);
        const m = Math.floor((ms/(1000*60))%60);
        const s = Math.floor((ms/1000)%60);
        return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
    }

    // Klik meja
    document.querySelectorAll('.menu-link').forEach(link=>{
        link.addEventListener('click', function(e){
            e.preventDefault();
            const nomorMeja = this.dataset.nomorMeja;
            const status = this.dataset.status;
            let lamaMain = '00:00:00';
            if(activeStatuses.includes(status)){
                lamaMain = this.querySelector('.stopwatch, .countdown').innerText;
            }
            window.location.href = `/list/${nomorMeja}?lama_main=${lamaMain}`;
        });
    });

    // Socket
    const socket = io("https://wasit.playandbreak.site",{path:"/socket.io/"});
    socket.on("mejaUpdate", (data)=>{
        const cardBody = document.querySelector(`.meja[data-nomor-meja="${data.nomor_meja}"]`)?.closest('.card-body');
        if(!cardBody) return;

        const mejaEl = cardBody.querySelector('.meja');
        let timerEl = cardBody.querySelector('.countdown, .stopwatch');

        if(timerEl.dataset.intervalId){
            clearInterval(timerEl.dataset.intervalId);
            delete timerEl.dataset.intervalId;
        }

        mejaEl.classList.remove('meja-green','meja-yellow','meja-red');

        if(data.status === 'selesai'){
            timerEl.innerHTML = '00:00:00';
            mejaEl.classList.add('meja-red');
        } else if(data.waktu_akhir){ // countdown berdasarkan waktu_akhir
            timerEl.className = 'countdown';
            startCountdown(cardBody, new Date(data.waktu_akhir).getTime(), new Date(data.server_now).getTime());
            mejaEl.classList.add('meja-yellow');
        } else if(['lanjut','tambahlanjut'].includes(data.status)){ // stopwatch dari waktu_mulai
            timerEl.className = 'stopwatch';
            startStopwatch(cardBody, new Date(data.start_time).getTime(), new Date(data.server_now).getTime());
            mejaEl.classList.add('meja-yellow');
        } else {
            timerEl.innerHTML = '00:00:00';
            mejaEl.classList.add('meja-green');
        }
    });

    // Countdown
    function startCountdown(cardBody, endTime, serverNow){
        const timerEl = cardBody.querySelector('.countdown');
        const offset = Date.now() - serverNow;
        const targetTime = endTime - offset;

        function updateCountdown(){
            const diff = targetTime - Date.now();
            if(diff <= 0){
                timerEl.innerHTML='00:00:00';
                const mejaEl = cardBody.querySelector('.meja');
                mejaEl.classList.remove('meja-yellow');
                mejaEl.classList.add('meja-red');
                clearInterval(timerEl.dataset.intervalId);
                return;
            }
            timerEl.innerHTML = formatTime(diff);
        }
        updateCountdown();
        timerEl.dataset.intervalId = setInterval(updateCountdown,1000);
    }

    // Stopwatch
    function startStopwatch(cardBody, startTime, serverNow){
        const timerEl = cardBody.querySelector('.stopwatch');
        const mejaEl = cardBody.querySelector('.meja');
        mejaEl.classList.add('meja-yellow');
        const offset = Date.now() - serverNow;
        const start = startTime + offset;

        function updateStopwatch(){
            const elapsed = Date.now() - start;
            timerEl.innerHTML = formatTime(elapsed);
        }
        updateStopwatch();
        timerEl.dataset.intervalId = setInterval(updateStopwatch,1000);
    }

    // Jalankan timer awal
    document.querySelectorAll('.card-body').forEach(cardBody=>{
        const mejaEl = cardBody.querySelector('.meja');
        const timerEl = cardBody.querySelector('.countdown, .stopwatch');
        const status = timerEl.dataset.status;
        const endTime = mejaEl.dataset.endTime ? new Date(mejaEl.dataset.endTime).getTime() : null;
        const startTime = mejaEl.dataset.startTime ? new Date(mejaEl.dataset.startTime).getTime() : null;
        const serverNow = new Date().getTime(); // bisa diganti server timestamp dari controller

        if(endTime){
            timerEl.className = 'countdown';
            startCountdown(cardBody, endTime, serverNow);
        } else if(['lanjut','tambahlanjut'].includes(status) && startTime){
            timerEl.className = 'stopwatch';
            startStopwatch(cardBody, startTime, serverNow);
        }
    });
});
</script>
@endsection
