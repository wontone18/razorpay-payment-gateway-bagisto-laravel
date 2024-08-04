<?php


namespace Wontonee\Razorpay\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Sales\Repositories\InvoiceRepository;
use Illuminate\Http\Request;
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
     * InvoiceRepository $invoiceRepository
     *
     * @var \Webkul\Sales\Repositories\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository,  InvoiceRepository $invoiceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Redirects to the paytm server.
     *
     * @return \Illuminate\View\View
     */

    public function redirect(Request $request)
    {

        $cart = Cart::getCart();
        $billingAddress = $cart->billing_address;
        include __DIR__ . '/../../razorpay-php/Razorpay.php';

        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount =  ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount

        $api = new Api(core()->getConfigData('sales.payment_methods.razorpay.key_id'), core()->getConfigData('sales.payment_methods.razorpay.secret'));



        //
        // We create an razorpay order using orders api
        // Docs: https://docs.razorpay.com/docs/orders
        //
        $orderData = [
            'receipt'         => "Receipt no. " . $cart->id,
            'amount'          => $total_amount * 100,
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];

        $razorpayOrder = $api->order->create($orderData);

        $razorpayOrderId = $razorpayOrder['id'];

        $_SESSION['razorpay_order_id'] = $razorpayOrderId;

        $request->session()->put('razorpay_order_id', $razorpayOrderId);

        $displayAmount = $amount = $orderData['amount'];

        $data = [
            "key"               => core()->getConfigData('sales.payment_methods.razorpay.key_id'),
            "amount"            => $orderData['amount'],
            "name"              => $billingAddress->name,
            "description"       => "RazorPay payment collection for the order - " . $cart->id,
            "image"             => "https://www.wontonee.com/wp-content/uploads/2020/12/wontonee-black.png",
            "prefill"           => [
                "name"              => $billingAddress->name,
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
            "callback_url" => route('razorpay.callback')
        ];

        $json = json_encode($data);
        return view('razorpay::razorpay-redirect')->with(compact('data', 'json'));
    }

    /**
     * verify for automatic 
     */
    public function verify(Request $request)
    {

        include __DIR__ . '/../../razorpay-php/Razorpay.php';
        $success = true;
        $error = "Payment Failed";


        if (empty($request->input('razorpay_payment_id')) === false) {
            $api = new Api(core()->getConfigData('sales.payment_methods.razorpay.key_id'), core()->getConfigData('sales.payment_methods.razorpay.secret'));
            try {
                // Please note that the razorpay order ID must
                // come from a trusted source (session here, but
                // could be database or something else)
                $attributes = array(
                    'razorpay_order_id' => $request->session()->get('razorpay_order_id'),
                    'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                    'razorpay_signature' =>  $request->input('razorpay_signature')
                );

                $api->utility->verifyPaymentSignature($attributes);
            } catch (SignatureVerificationError $e) {
                $success = false;
                $error = 'Razorpay Error : ' . $e->getMessage();
            }
        }
        if ($success === true) {
            $cart = Cart::getCart();
            $data = (new OrderResource($cart))->jsonSerialize(); // new class v2.2
            $order = $this->orderRepository->create($data);
           // $order = $this->orderRepository->create(Cart::prepareDataForOrder()); // removed for v2.2
            $this->orderRepository->update(['status' => 'processing'], $order->id);
            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }
            Cart::deActivateCart();
            session()->flash('order_id', $order->id); // line instead of $order in v2.1
            // Order and prepare invoice
            return redirect()->route('shop.checkout.onepage.success');
        } else {
            session()->flash('error', 'Razorpay payment either cancelled or transaction failure.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }
    /**
     * Prepares order's invoice data for creation.
     *
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ["order_id" => $order->id,];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
