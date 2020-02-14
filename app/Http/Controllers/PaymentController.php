<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaypalOrderRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaymentController extends Controller
{
    private $api_context;

    public function __construct()
    {
        /** PayPal api context **/
        $paypal_conf =  \Config::get('paypal');
        $this->api_context = new ApiContext(
            new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret']
            )
        );
        $this->api_context->setConfig($paypal_conf['settings']);
    }


    public function payWithpaypal(PaypalOrderRequest $request)
    {
        $product_name = $request->product_name;
        $price = $request->price_per_unit;
        $quantity = $request->quantity;
        $total_price = $price * $quantity;

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $product = new Item();
        $product->setName($product_name)
            ->setCurrency('USD')
            ->setQuantity($quantity)
            ->setPrice($price);
        $item_list = new ItemList();
        $item_list->setItems(array($product));
        dd($item_list);

        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($total_price);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('status'))
            ->setCancelUrl(URL::route('status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($this->api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
                \Session::put('error', 'Connection timeout');
                return Redirect::route('/');
            } else {
                \Session::put('error', 'Some error occur, sorry for inconvenient');
                return Redirect::route('/');
            }
        }
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        \Session::put('paypal_payment_id', $payment->getId());
        if (isset($redirect_url)) {
            return Redirect::away($redirect_url);
        }
        \Session::put('error', 'Unknown error occurred');
        return Redirect::route('/');
    }


    public function getPaymentStatus()
    {
        $payment_id = Session::get('paypal_payment_id');
        Session::forget('paypal_payment_id');

        if (empty(request('PayerID')) || empty(request('token'))) {
            \Session::put('error', 'Payment failed');
            return Redirect::route('/');
        }

        $payment = Payment::get($payment_id, $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId(request('PayerID'));
        $result = $payment->execute($execution, $this->api_context);

        if ($result->getState() == 'approved') {
            \Session::put('success', 'Payment success');
            return Redirect::route('/');
        }
        \Session::put('error', 'Payment failed');
        return Redirect::route('/');
    }
}
