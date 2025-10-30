<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Redirect;

class KnetController extends Controller
{

    public function pay()
    {
        // Creating an environment
        $user = Auth::user();
        if(Session::has('payment_type')) {
            if(Session::get('payment_type') == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = $combined_order->grand_total;
                $paymentData = [
                    'order' => Session::get('combined_order_id'),
                    'user' => Auth::user()->id,
                    'mobile_number' => $user->phone,
                    'email' => $user->email,
                    'amount' => $amount,
                    'payment_type'=>"1",
                    'callback_url' => route('knet.callback')
                  ];
            }
            elseif (Session::get('payment_type') == 'wallet_payment') {
                $amount = Session::get('payment_data')['amount'];
                $paymentData = [
                    'order' => rand(10000,99999),
                    'user' => Auth::user()->id,
                    'mobile_number' => $user->phone,
                    'email' => $user->email,
                    'amount' => $amount,
                    'payment_type'=>"1",
                    'callback_url' => route('knet.callback')
                  ];
            }
            elseif (Session::get('payment_type') == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                $amount = $customer_package->amount;
                $paymentData = [
                    'order' => Session::get('payment_data')['customer_package_id'],
                    'user' => Auth::user()->id,
                    'mobile_number' => $user->phone,
                    'email' => $user->email,
                    'amount' => $amount,
                    'payment_type'=>"1",
                    'callback_url' => route('knet.callback')
                  ];
            }
            elseif (Session::get('payment_type') == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                $amount = $seller_package->amount;
                $paymentData = [
                    'order' => Session::get('payment_data')['seller_package_id'],
                    'user' => Auth::user()->id,
                    'mobile_number' => $user->phone,
                    'email' => $user->email,
                    'amount' => $amount,
                    'payment_type'=>"1",
                    'callback_url' => route('knet.callback')
                  ];
            }
        }

        try {
            // Call API with your client and get a response for your call
            return $this->initiateTransation($paymentData);
        }catch (\Exception $ex) {
            flash(translate('Something was wrong'))->error();
            return redirect()->route('home');
        }
    }

    public function callback(Request $request){
        //$transaction = PaytmWallet::with('receive');

        //$response = $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm
		$transaction = $this->responseCallback($_GET['encrp']);
        if(isset($transaction->Status) && $transaction->Status != "" && $transaction->Status == "1"){
            if($request->session()->has('payment_type')){
                if($request->session()->get('payment_type') == 'cart_payment'){
                    return (new CheckoutController)->checkout_done($request->session()->get('combined_order_id'), json_encode($transaction));
                }
                elseif ($request->session()->get('payment_type') == 'wallet_payment') {
                    return (new WalletController)->wallet_payment_done($request->session()->get('payment_data'), json_encode($transaction));
                }
                elseif ($request->session()->get('payment_type') == 'customer_package_payment') {
                    return (new CustomerPackageController)->purchase_payment_done($request->session()->get('payment_data'), json_encode($transaction));
                }
                elseif ($request->session()->get('payment_type') == 'seller_package_payment') {
                    return (new SellerPackageController)->purchase_payment_done($request->session()->get('payment_data'), json_encode($transaction));
                }
            }
        }else {
            //flash(translate('Payment cancelled'))->error();
			$request->session()->forget('order_id');
            $request->session()->forget('payment_data');
            flash(translate('your order successfully placed. but your payment was failed / Declined.'))->success();
        	return redirect()->route('home');
        	//return back();
        }
        //$transaction->getResponseMessage(); //Get Response Message If Available
        //get important parameters via public methods
        //$transaction->getOrderId(); // Get order id
        //$transaction->getTransactionId(); // Get transaction id
    }
    
    public function initiateTransation($data){
		$payment_test_mode = env('KNET_ENVIRONMENT');
        if ($payment_test_mode == 'development') {
            $URL = "https://pgtest.cbk.com";
        } else {
            $URL = "https://pg.cbk.com";
        }
        $ClientId = env('KNET_CLIENT_ID');
        $ClientSecret = env('KNET_CLIENT_SECRET');
        $encrp_key = env('KNET_ENCRP_KEY');
		if ($AccessToken = $this->getAccessToken()) {
            $accessToken = $AccessToken;
            $cbk_payment_url = $URL . "/ePay/pg/epay?_v=" . $accessToken;

            $amount = number_format($data['amount'], 2, '.', '');
            $lang = "en";
            $paymentType = $data['payment_type'];
            $paymentToken =rand(000000,999999);
            $orderID = $data['order'];
            $paymentURL = $data['callback_url'];
            $formData = array(
                'tij_MerchantEncryptCode' => $encrp_key,
                'tij_MerchAuthKeyApi' => $accessToken,
                'tij_MerchantPaymentLang' => $lang,
                'tij_MerchantPaymentAmount' => $amount,
                'tij_MerchantPaymentTrack' => $paymentToken,
                'tij_MerchantPaymentRef' => "",
                'tij_MerchantUdf1' => $orderID,
                'tij_MerchantUdf2' => "KWD",
                'tij_MerchantUdf3' => "",
                'tij_MerchantUdf4' => "",
                'tij_MerchantUdf5' => "",
                'tij_MerchPayType' => $paymentType,
                'tij_MerchReturnUrl' => $paymentURL
            );
            // to prevent multiple entries in DB
            // $this->db->query("DELETE FROM " . DB_PREFIX . "cbk_payments WHERE order_id = '" . $this->session->data['order_id'] . "'");
            // insert into cbk payments
           // $this->db->query("INSERT " . DB_PREFIX . "cbk_payments SET order_id = '" . $this->session->data['order_id'] . "', customer_id = '" . $order_info['customer_id'] . "', payment_url = '" . $paymentURL . "', token = '" . $paymentToken . "', payment_type = $paymentType, total = '" . $amount . "', currency_code = '" . $this->session->data['currency'] . "', result = '', date_added = NOW()");

            $url = $cbk_payment_url;
            $form = "<form id='pgForm' method='post' action='$url' enctype='application/x-www-form-urlencoded'>";
            foreach ($formData as $k => $v) {
                $form .= "<input type='hidden' name='$k' value='$v'>";
            }
            $form .= "</form><div style='position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%;text-align:center'>Redirecting to Payment Gateway ... <br> <b> DO NOT REFRESH</b></div><script type='text/javascript'>
    document.getElementById('pgForm').submit();
</script>";

            echo $form;
        } else {
            $data['result'] = "Authentication Failed";
            flash(translate('Payment cancelled'))->error();
        }
	}
    public function responseCallback($data){
		$encrp = $data;
				$payment_test_mode = env('KNET_ENVIRONMENT');
        if ($payment_test_mode == 'development') {
            $URL = "https://pgtest.cbk.com";
        } else {
            $URL = "https://pg.cbk.com";
        }
        $ClientId = env('KNET_CLIENT_ID');
        $ClientSecret = env('KNET_CLIENT_SECRET');
        $ENCRP_KEY = env('KNET_ENCRP_KEY');
        if ($encrp == "") {
            $url = $_SERVER[REQUEST_URI];
            $url_end = end(explode('?', $url));
            $url_params = explode('=', $url_end);
            if ($url_params[0] == "encrp") {
                $encrp = $url_params[1];
            }
        }
		if ($encrp != "" && $AccessToken = $this->getAccessToken()) {
            $url = $URL . "/ePay/api/cbk/online/pg/GetTransactions/" . $encrp . "/" . $AccessToken;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_ENCODING => "",
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic ' . base64_encode($ClientId . ":" . $ClientSecret),
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);


            $paymentDetails = json_decode($response);
			$insertData = array();
			$order_id = $paymentDetails->MerchUdf1;
            $payment_token = $paymentDetails->PayId;
            $payment_id = $paymentDetails->TrackId;
            $Amount = $paymentDetails->Amount;
            $PayId = $paymentDetails->PayId;
            $TrackId = $paymentDetails->TrackId;
            $Status = $paymentDetails->Status;
            $Message = $paymentDetails->Message;
            $payType = $paymentDetails->PayType;
            $PaymentId = $paymentDetails->PaymentId;
            $AuthCode = $paymentDetails->AuthCode;
            $PostDate = $paymentDetails->PostDate;
            $ReferenceId = $paymentDetails->ReferenceId;
            $TransactionId = $paymentDetails->TransactionId;
                        $data=array(
                'Amount'=>$Amount,
                "PayId"=>$PayId,
                "TrackId"=>$TrackId,
                "Status"=>$Status,
                'Message'=>$Message,
                "payType"=>$payType,
                "PaymentId"=>$PaymentId,
                "AuthCode"=>$AuthCode,
                "PostDate"=>$PostDate,
                "ReferenceId"=>$ReferenceId,
                "TransactionId"=>$TransactionId,
                "payment_url"=>$_SERVER['REQUEST_URI'],
                "payment_id"=>$payment_id,
                "token" =>$payment_token
                );
            if ($paymentDetails->Status != "" && $paymentDetails->Status == "1") {
                //return $paymentDetails;
                $data['paid_on'] = NOW();
                $data['result'] = 'SUCCESS';
                $data['paid'] = '1';
                // update cbk payment
                DB::table('cbk_payments')->insert($data);
            } else {
                // update cbk payment
                $data['paid_on'] = 'NOW()';
                $data['result'] = 'DECLINED / EXPIRED / CANCELLED';
                $data['paid'] = '0';
                // update cbk payment
                DB::table('cbk_payments')->insert($data);
            }
            return $paymentDetails;
        } else {
            // update cbk payment
                return false;
        }
	}
	/**
	* call function of getAccessToken
	*/
	function getAccessToken()
    {
        $payment_test_mode = env('KNET_ENVIRONMENT');
        if ($payment_test_mode == 'development') {
            $URL = "https://pgtest.cbk.com";
        } else {
            $URL = "https://pg.cbk.com";
        }
        $ClientId = env('KNET_CLIENT_ID');
        $ClientSecret = env('KNET_CLIENT_SECRET');
        $ENCRP_KEY = env('KNET_ENCRP_KEY');

        $postfield = array(
            "ClientId" => $ClientId,
            "ClientSecret" => $ClientSecret,
            "ENCRP_KEY" => $ENCRP_KEY
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $URL . "/ePay/api/cbk/online/pg/merchant/Authenticate",
            CURLOPT_ENCODING => "",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_POSTFIELDS => json_encode($postfield),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($ClientId . ":" . $ClientSecret),
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $authenticateData = json_decode($response);

        if ($authenticateData->Status == "1") {
            //save access token till expiry
            return $authenticateData->AccessToken;
        } else {
            return false;
        }
    }
}
