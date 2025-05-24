<?php

namespace Modules\Transaction\Services;

use Modules\Order\Entities\Order;

class SADADPaymentService
{

    protected $accessToken = 'asdasdqweczxvz';
    protected $paymentMode = 'test';
    protected $paymentUrl = "https://apisandbox.sadadpay.net/api/";

    public function __construct()
    {
        $this->client_key = env('SADAD_PAY_SANDBOX_KEY');
        $this->client_secret = env('SADAD_PAY_SANDBOX_SECRET');
        if (config('setting.supported_payments.sadad.payment_mode') == 'live_mode') {
            $this->paymentMode = 'live';
            $this->paymentUrl = "https://api.sadadpay.net/api/";
            $this->client_key = env('SADAD_PAY_LIVE_KEY');
            $this->client_secret = env('SADAD_PAY_LIVE_SECRET');
        }

        $this->refresh_token = $this->getRefreshToken();
        $this->accessToken = $this->getAccessToken();
    }

    public function getRefreshToken()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $this->paymentUrl.'User/GenerateRefreshToken',[
            'headers' => [
                'Accept'     => 'application/json',
                'Authorization'      => 'Basic '. base64_encode($this->client_key.':'.$this->client_secret),
            ],
        ]);

        $payload = json_decode($response->getBody()->getContents());
        if(isset($payload->isValid) && $payload->isValid){
            return $payload->response->refreshToken;
        }
        return false;
    }

    public function getAccessToken()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $this->paymentUrl.'User/GenerateAccessToken',[
            'headers' => [
                'Accept'     => 'application/json',
                'Authorization'      => 'Bearer '. $this->refresh_token,
            ],
        ]);

        $payload = json_decode($response->getBody()->getContents());
        if(isset($payload->isValid) && $payload->isValid){
            return $payload->response->accessToken;
        }
        return false;
    }


    public function createInvoice($orderId)
    {
        $invoiceData = [];
        $orderObj = Order::find($orderId);
        if($orderObj){
            $baseData['amount'] = $orderObj->total;
            $baseData['currency_Code'] = 'KWD';
            $baseData['ref_Number'] = 'order #'.$orderId;
            $baseData['customer_Name'] = auth()->check() ? auth()->user()->name : 'Guest User';
            $baseData['customer_Email'] = auth()->check() ? auth()->user()->email : 'guest_user@example.com';
            $baseData['customer_Mobile'] = auth()->check() ? (auth()->user()->calling_code ?? '' . auth()->user()->mobile ?? '') : '00000000';
            $invoiceData[] = $baseData;
            if(!$orderObj->sadad_invoice_id){
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', $this->paymentUrl.'Invoice/insert',[
                    'headers' => [
                        'content-type' => 'application/*+json',
                        'Authorization'      => 'Bearer '. $this->accessToken,
                    ],
                    'body'  => json_encode([
                        'invoices'  => $invoiceData
                    ]),
                ]);
                $payload = json_decode($response->getBody()->getContents());
                if(isset($payload->isValid) && $payload->isValid){
                    $orderObj->update(['sadad_invoice_id'=>$payload->response->invoiceId]);
                }
            }
        }
        return $this->getInvoiceInfo($orderObj->sadad_invoice_id);
    }

    public function getInvoiceInfo($invoice_id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->paymentUrl.'Invoice/getbyid?id='.$invoice_id,[
            'headers' => [
                'Authorization'      => 'Bearer '. $this->accessToken,
            ],

        ]);
        $payload = json_decode($response->getBody()->getContents());
        if(isset($payload->isValid) && $payload->isValid){
            return $payload->response;
        }
        return false;
    }

    public function paymentUrls($orderType)
    {
        if ($orderType == 'api-order') {
            $url['success'] = url(route('api.orders.success'));
            $url['failed'] = url(route('api.orders.failed'));
            $url['webhooks'] = url(route('api.orders.webhooks'));
        } else {
            $url['success'] = url(route('frontend.orders.success'));
            $url['failed'] = url(route('frontend.orders.failed'));
            $url['webhooks'] = url(route('frontend.orders.webhooks'));
        }
        return $url;
    }
}
