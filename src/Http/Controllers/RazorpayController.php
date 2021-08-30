<?php


namespace Wontonee\Razorpay\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Config;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    /**
     * OrderRepository $orderRepository
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Redirects to the paytm server.
     *
     * @return \Illuminate\View\View
     */

    public function redirect()
    {

        $cart = Cart::getCart();
        $billingAddress = $cart->billing_address;
        include __DIR__ . '/../../razorpay-php/Razorpay.php';

        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount =  ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount

        $api = new Api(core()->getConfigData('sales.paymentmethods.razorpay.key_id'), core()->getConfigData('sales.paymentmethods.razorpay.secret'));

        //
        // We create an razorpay order using orders api
        // Docs: https://docs.razorpay.com/docs/orders
        //
        $orderData = [
            'receipt'         => $cart->id,
            'amount'          => $total_amount, // 2000 rupees in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];

        $razorpayOrder = $api->order->create($orderData);

        $razorpayOrderId = $razorpayOrder['id'];

        $_SESSION['razorpay_order_id'] = $razorpayOrderId;

        $displayAmount = $amount = $orderData['amount'];

        $data = [
            "key"               => core()->getConfigData('sales.paymentmethods.razorpay.key_id'),
            "amount"            => $amount,
            "name"              => $billingAddress->name,
            "description"       => "-",
            "image"             => "https://s29.postimg.org/r6dj1g85z/daft_punk.jpg",
            "prefill"           => [
                "name"              => "Daft Punk",
                "email"             => $billingAddress->email,
                "contact"           => $billingAddress->phone,
            ],
            "notes"             => [
                "address"           => $billingAddress->address,
                "merchant_order_id" => $cart->id,
            ],
            "theme"             => [
                "color"             => "#F37254"
            ],
            "order_id"          => $razorpayOrderId,
        ];

        $json = json_encode($data);
        return view('razorpay::razorpay-redirect')->with(compact('data','json'));
    }

    /**
     * verify for automatic 
     */
    public function verify()
    {

        $success = true;

        $error = "Payment Failed";

        if (empty($_POST['razorpay_payment_id']) === false) {
            $api = new Api(core()->getConfigData('sales.paymentmethods.razorpay.key_id'), core()->getConfigData('sales.paymentmethods.razorpay.secret'));

            try {
                // Please note that the razorpay order ID must
                // come from a trusted source (session here, but
                // could be database or something else)
                $attributes = array(
                    'razorpay_order_id' => $_SESSION['razorpay_order_id'],
                    'razorpay_payment_id' => $_POST['razorpay_payment_id'],
                    'razorpay_signature' => $_POST['razorpay_signature']
                );

                $api->utility->verifyPaymentSignature($attributes);
            } catch (SignatureVerificationError $e) {
                $success = false;
                $error = 'Razorpay Error : ' . $e->getMessage();
            }
        }

        if ($success === true) {
            $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>";
        } else {
            $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
        }

        echo $html;
    }
}
