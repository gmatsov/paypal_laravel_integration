<head>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">
</head>

<body>
    <div>
        <div class="text-justify">
            <h1>PayPal single product integration demo</h1>
        </div>

        @if ($message = Session::get('success'))
        <div class="bg-success">
            <span onclick="this.parentElement.style.display='none'">&times;</span>
            <p>{!! $message !!}</p>
        </div>
        <?php Session::forget('success');?>
        @endif
        @if ($message = Session::get('error'))
        <div class="bg-error">
            <span onclick="this.parentElement.style.display='none'">&times;</span>
            <p>{!! $message !!}</p>
        </div>
        <?php Session::forget('error');?>
        @endif
        <div class="row justify-content-center">
            <form method="POST" class="col-md-4" id="payment-form" action="/payment/add-funds/paypal">
                {{ csrf_field() }}
                <h2>Payment Form</h2>
                <div class="form-group">
                    <label for="product_name"><strong> Product Name</strong></label>
                    <input class="form-control" id="product_name" type="text" name="product_name">
                </div>
                <div class="form-group">
                    <label for="price_per_unit"><strong>Enter Price per unit</strong></label>
                    <input class="form-control" id="price_per_unit" name="price_per_unit" type="text"></p>
                </div>
                <div class="form-group">
                    <label for="quantity"><strong>Quantity</strong></label>
                    <input class="form-control" id="quantity" name="quantity" type="text"></p>
                </div>

                <button class="btn btn-primary">Pay with PayPal</button></p>
            </form>
        </div>
    </div>
</body>