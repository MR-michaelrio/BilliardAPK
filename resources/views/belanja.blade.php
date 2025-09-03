@extends('layout.main')

@section('content')
<style>
    .meja {
        color: black;
        background-color: #72fc89;
        width: auto;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
    }

    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }

    .table {
        width: 100%;
        table-layout: fixed;
    }

    .table th {
        width: 20%;
    }

    .countdown {
        font-weight: bold;
        color: red;
    }
</style>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="id_table">Table ID</label>
                            <!-- <input type="text" id="id_table" class="form-control" value="0"> -->
                            <select class="form-control select2" id='id_table' name='id_table' onchange="myFunction()"
                                style="width: 100%;">
                                <option value='0'>0</option>
                                @foreach($rental as $m)
                                <option value='{{$m->id_player}}'>{{ $m->no_meja }} | {{ $m->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Cart items will be injected here by JavaScript -->
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-6">
                                <button class="btn btn-danger" id="cancel-button">Cancel</button>
                            </div>
                            <div class="col-6 text-right">
                                <button class="btn btn-secondary" id="save-button"
                                    style="display: none;">Simpan</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-right">
                                <h3>Total: <span id="total-price">0</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body"><div class="col-12 table-responsive">
                        <table id="example3" class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <th>{{ $product['nama_produk'] }}</th>
                                    <th><button class="btn btn-success add-to-cart"
                                            data-name="{{ $product['nama_produk'] }}"
                                            data-price="{{ $product['harga'] }}">Add</button></th>
                                </tr>
                                @endforeach
                            </tbody>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const idTable = document.getElementById('id_table').value;
    console.log(idTable);

    function myFunction() {
        const saveButton = document.getElementById('save-button');

        var x = document.getElementById("id_table").value;
        if (x > 0) {
            saveButton.style.display = 'inline-block';
        } else {
            saveButton.style.display = 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const cartItems = [];
        const cartItemsContainer = document.getElementById('cart-items');
        const totalPriceElement = document.getElementById('total-price');

        function updateCart() {
            return new Promise((resolve) => {
        // Existing updateCart logic
        cartItemsContainer.innerHTML = '';
        let totalPrice = 0;
        cartItems.forEach((item, index) => {
            totalPrice += item.price * item.quantity;
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${item.name}</td>
            <td>
                <input type="number" class="form-control quantity-input" value="${item.quantity}" data-name="${item.name}">
            </td>
            <td>
                <textarea type="text" class="form-control note-input" value="${item.note}" data-name="${item.name}"></textarea>
            </td>
            <td>
                <button class="btn btn-danger remove-from-cart" data-index="${index}">X</button>
            </td>
        `;
            cartItemsContainer.appendChild(row);
        });
        totalPriceElement.textContent = totalPrice;

        // Resolve the promise when done
        resolve();
    });
        }

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function () {
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));
                const existingItem = cartItems.find(item => item.name === name);
                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    cartItems.push({
                        name,
                        price,
                        quantity: 1,
                        note: ""
                    });
                }
                updateCart();
            });
        });

        document.getElementById('cancel-button').addEventListener('click', function () {
            cartItems.length = 0;
            updateCart();
        });

        document.getElementById('save-button').addEventListener('click', function() {
            document.getElementById('loading').style.display = 'flex';
            const idTable = document.getElementById('id_table').value;
            fetch('{{ route("belanja.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_table: idTable, items: cartItems })
            })
            .then(response => response.json())
            .then(data => {
                // Hide the spinner
                document.getElementById('loading').style.display = 'none';

                if (data.success) {
                    showAlert('Success', 'Order submitted successfully', 'success')
                    cartItems.length = 0;
                    updateCart();
                } else {
                    alert('There was an error saving the order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Hide the spinner in case of an easdrror
                document.getElementById('loading').style.display = 'none';
                alert('There was an error saving the order. Please check the console for more details.');
            });
        });


        cartItemsContainer.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-from-cart')) {
                const index = parseInt(event.target.getAttribute('data-index'));
                cartItems.splice(index, 1);
                updateCart();
            }
        });

        cartItemsContainer.addEventListener('change', function (event) {
            if (event.target.classList.contains('quantity-input')) {
                const name = event.target.getAttribute('data-name');
                const quantity = parseInt(event.target.value);
                const item = cartItems.find(item => item.name === name);
                if (item) {
                    item.quantity = quantity;
                }
                updateCart();
            }
            if (event.target.classList.contains('note-input')) {
                const name = event.target.getAttribute('data-name');
                const note = event.target.value;
                const item = cartItems.find(item => item.name === name);
                if (item) {
                    item.note = note;
                }
            }
        });
    });
</script>

@endsection