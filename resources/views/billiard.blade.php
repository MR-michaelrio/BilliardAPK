@extends('layout.main')
@section('content')
<style>
    .meja {
        color: black;
        width: 100%;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
    }

    .meja-green {
        background-color: #72fc89;
    }

    .meja-yellow {
        background-color: #ffd666;
    }

    .meja-red {
        background-color: #ff6666;
    }

    .countdown, .stopwatch {
        font-weight: bold;
        color: red;
        text-align: center;
        font-size: 24px;
    }

    .card {
        margin-bottom: 20px;
    }

    .divider {
        height: 20px;
        background-color: black;
        margin: 20px 0;
    }

    .kasir {
        writing-mode: vertical-rl;
        text-align: center;
        background-color: #5f5f5f;
        color: white;
        padding: 20px;
        font-size: 24px;
        padding:100px 10px;
        /* display: flex; */
        /* align-items: center; */
        justify-content: center;
        /* position: absolute; */
        /* top: 50%; */
    }
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
                                            <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" 
                                                data-end-time="{{ $mi['waktu_akhir'] }}" 
                                                data-start-time="{{ $mi['waktu_mulai'] }}" 
                                                data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                                    Meja {{ $mi['nomor_meja'] }}
                                            </div>
                                            <div class="{{ $mi['status'] === 'lanjut' ? 'stopwatch' : 'countdown' }}" data-status="{{ $mi['status'] }}">
                                                {{ $mi['status'] === 'lanjut' ? '00:00:00' : ($mi['waktu_akhir'] ?? 'N/A') }}
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
    const links = document.querySelectorAll('.menu-link');

    // Klik meja → redirect ke list + lama_main
    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const nomorMeja = this.getAttribute('data-nomor-meja');
            const status = this.getAttribute('data-status');
            let lamaMain = '00:00:00';
            if (status === 'lanjut') {
                lamaMain = this.querySelector('.stopwatch').innerText;
            }
            window.location.href = `/list/${nomorMeja}?lama_main=${lamaMain}`;
        });
    });

    // Socket listener
    const socket = io("http://185.199.53.230:3001");
    socket.on("mejaUpdate", (data) => {
        console.log("Update meja diterima:", data);
        const cardBody = document.querySelector(`.meja[data-nomor-meja="${data.nomor_meja}"]`)?.closest('.card-body');
        if (!cardBody) return;

        const mejaEl = cardBody.querySelector('.meja');
        let timerEl = cardBody.querySelector('.countdown, .stopwatch');

        // Hentikan interval lama kalau ada
        if (timerEl.dataset.intervalId) {
            clearInterval(timerEl.dataset.intervalId);
            delete timerEl.dataset.intervalId;
        }

        // Reset warna & timer
        mejaEl.classList.remove('meja-green', 'meja-yellow', 'meja-red');

        if (data.status === "jalan") {
            // countdown
            timerEl.className = 'countdown';
        if (data.waktu_akhir) startCountdown(cardBody, data.waktu_akhir);
            mejaEl.classList.add('meja-yellow');
        } else if (data.status === "lanjut") {
            // stopwatch
            timerEl.className = 'stopwatch';
            if (data.start_time) startStopwatch(cardBody, data.start_time);
            mejaEl.classList.add('meja-yellow');
        } else {
            timerEl.innerHTML = "N/A";
            mejaEl.classList.add('meja-green');
        }

    });

    // Countdown function
    function startCountdown(cardBody, endTime) {
        const timerEl = cardBody.querySelector('.countdown');

        function updateCountdown() {
            const now = Date.now();
            const end = new Date(endTime).getTime();
            const diff = end - now;

            if (diff <= 0) {
                timerEl.innerHTML = "00:00:00";
                const mejaEl = cardBody.querySelector('.meja');
                mejaEl.classList.remove('meja-yellow');
                mejaEl.classList.add('meja-red');
                clearInterval(timerEl.dataset.intervalId);
                return;
            }

            const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const m = Math.floor((diff / (1000 * 60)) % 60);
            const s = Math.floor((diff / 1000) % 60);
            timerEl.innerHTML =
                `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        updateCountdown();
        const id = setInterval(updateCountdown, 1000);
        timerEl.dataset.intervalId = id;
    }

    // Stopwatch function
    function startStopwatch(cardBody, startTime) {
        const timerEl = cardBody.querySelector('.stopwatch');
        const mejaEl = cardBody.querySelector('.meja');
        mejaEl.classList.add('meja-yellow');

        const start = new Date(startTime).getTime();

        function updateStopwatch() {
            const now = Date.now();
            const elapsed = now - start;

            const h = Math.floor((elapsed / (1000 * 60 * 60)) % 24);
            const m = Math.floor((elapsed / (1000 * 60)) % 60);
            const s = Math.floor((elapsed / 1000) % 60);
            timerEl.innerHTML =
                `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        updateStopwatch();
        const id = setInterval(updateStopwatch, 1000);
        timerEl.dataset.intervalId = id;
    }

    // Jalankan timer awal sesuai data database
    document.querySelectorAll('.card-body').forEach(cardBody => {
        const status = cardBody.querySelector('.countdown, .stopwatch').dataset.status;
        if (status === 'lanjut') {
            const startTime = cardBody.querySelector('.meja').dataset.startTime;
            if (startTime) startStopwatch(cardBody, startTime);
        } else {
            const endTime = cardBody.querySelector('.meja').dataset.endTime;
            if (endTime) startCountdown(cardBody, endTime);
        }
    });
});
</script>


@endsection
