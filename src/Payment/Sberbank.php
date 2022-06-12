<?php

namespace Goldmangroup\Sberbank\Payment;

use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Http;
use Webkul\Sales\Repositories\OrderRepository;
//use Barryvdh\Debugbar\Facades\Debugbar as Debugbar;

class Sberbank extends Payment
{
    protected $code = 'sberbank';
    protected $clientId;
    protected $clientSecret;

    public function __construct(
        protected OrderRepository $orderRepository
    )
    {
        $this->initialize();
    }

    public function getRedirectUrl()
    {
        return $this->generatePaymentUrl();
    }

    public function getPenny($price) {
        return ($price*100);
    }

    public function generatePaymentUrl() {

        Cart::collectTotals();

        $order = $this->orderRepository->create(Cart::prepareDataForOrder());
        $this->orderRepository->update(['status' => 'pending'], $order->id);


        $apiURL = 'https://securepayments.sberbank.ru/payment/rest/register.do';


        $orderBundle = [];
        $i = 1;
        $amount = 0;

        $cartItems = $this->getCartItems();

        foreach ($cartItems as $cartItem) {

            $itemPrice = $this->getPenny($cartItem->price);
            $itemAmount = $itemPrice * $cartItem->quantity;

            $orderBundle[] = [
                'positionId' => $i,
                'name' => $cartItem->name,
                'quantity' => [
                    'value' => $cartItem->quantity,
                    'measure' => 'шт'
                ],
                'itemAmount' => $itemAmount,
                'itemCode' => $cartItem->product_id,
                'itemPrice' => $itemPrice,
            ];

            $amount += $itemAmount;
            $i++;
        }

        $postInput = [
            'userName' => $this->clientId,
            'password' => $this->clientSecret,
            'orderNumber' => 'Bagisto-' . $order->id,
            'orderBundle' => json_encode([ 'cartItems' => [ 'items' => $orderBundle ] ], JSON_UNESCAPED_UNICODE),
            'amount' => $amount,
            'returnUrl' => url('/sberbank/success/' . $order->id),
            'failUrl' => url('/checkout/fail/' . $order->id),
            'description' => 'Заказ №' . $order->id . ' на ' .  url('/')
        ];


        $response = Http::asForm()->post($apiURL, $postInput);
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);

        //Debugbar::info($responseBody);

        $url = url('/checkout/fail/' . $order->id);
        if (isset($responseBody['formUrl'])) $url = $responseBody['formUrl'];

        return $url;


    }

    protected function initialize()
    {
        $this->clientId = $this->getConfigData('client_id') ?: '';
        $this->clientSecret = $this->getConfigData('client_secret') ?: '';
    }





}
