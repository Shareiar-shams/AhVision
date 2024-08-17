<?php

namespace App\Http\Controllers\User\Payment;

use App\Helpers\CurrencyConverter;
use App\Http\Controllers\Controller;
use App\Mail\OrderInvoiceMail;
use App\Models\Admin\Order;
use App\Models\Admin\Product;
use App\Models\Admin\Transection;
use App\Models\Admin\coupon;
use App\Models\User;
use App\Models\User\BkashPaySessionData;
use App\Models\User\OrderedItem;
use App\Services\OrderService;
use App\Services\PaymentProcessingService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Session;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $inv = uniqid();
        $session_id = Session::getId();
        $order_form_data = session()->get('order_data');

        $amount = CurrencyConverter::convertCurrency($order_form_data['total_cost']);

        // 2. Create PayPal client instance
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        // 3. Set currency (optional, adjust as needed)
        $provider->setCurrency('USD');
        $paypalToken = $provider->getAccessToken();

        // 4. Create PayPal order
        $order = $provider->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $provider->getCurrency(),
                        'value' => $amount,
                    ],
                    'description' => 'Purchase Product From AHknoxo with this invoice number, Invoice:'.$request->invoice,
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'), // Your application name
                'return_url'=> route('CheckoutsuccessPaypalTransaction'),
                'cancel_url'=> route('CheckoutcancelPaypalTransaction')
            ],
        ]);
        // 5. Handle successful order creation
        if ( isset($order['id']) && $order['id'] != null && $order['status'] === 'CREATED') {
            $user = User::find(Auth::id());
        
            $json_data = array(
                'order_type' => 'Ecommerce',
                'order_quantity' => \Cart::session(Session::getId())->getTotalQuantity(),
                'total' => $order_form_data['total_cost'],
                'user_id' => Auth::user()->id,
                'invoice' => $request->invoice,
                'name' => $order_form_data['billing_first_name'].' '.$order_form_data['billing_last_name'],
                'email' => $order_form_data['billing_email'],
                'phone' => $order_form_data['billing_phone'],
                'company' => $order_form_data['billing_company'],
                'billing_address' => $order_form_data['billing_address'],
                'billing_town' => $order_form_data['billing_town_city'],
                'billing_postal_code' => $order_form_data['postal_code'],
                'order_comments' => $order_form_data['order_notes'],

                'shipping_name' => $order_form_data['shipping_first_name'].' '.$order_form_data['shipping_last_name'],
                'shipping_email' => $order_form_data['shipping_email'],
                'shipping_phone' => $order_form_data['shipping_phone'],
                'shipping_company' => $order_form_data['shipping_company'],
                'shipping_address' => $order_form_data['shipping_address'],
                'shipping_town_city' => $order_form_data['shipping_town_city'],
                'shipping_postal_code' => $order_form_data['shipping_postal_code'],
                'shipping_title' => $order_form_data['shipping_title'],
                'shipping_cost' => $order_form_data['shipping_cost'],
            );
            $session_data = json_encode($json_data);

            $find = BkashPaySessionData::where('user_id', Auth::user()->id)->first();

            if(empty($find)){
                $item = new BkashPaySessionData();
                
            }else{
                $item = BkashPaySessionData::find($find->id);
                
            }
            $item->user_id  = Auth::user()->id;
            $item->session_name  = "ecommerce_order_payment";
            $item->session_data = $session_data;
            $item->save();

            foreach ($order['links'] as $key => $link) {
                if($link['rel'] === 'approve'){
                    return redirect()->away($link['href']);
                }
            }
        } else {
            // Handle errors (refer to PayPal documentation for error codes)
            // return back()->withErrors(['error' => $order->message]);
            return redirect()->route('CheckoutcancelPaypalTransaction');
        }
    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */

    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);
        $transectionId = $response['id'];
        $response['payerId'] = $request->PayerID;
        $type = 'success';
        $tracking_id = uniqid();
        if(isset($response['id']) && $response['id'] != null && $response['status'] === "COMPLETED"){
            $find = Auth::user()->bkashSession;

            if ( isset($find) && $find->session_name == 'ecommerce_order_payment' ) {

                $data = json_decode($find->session_data);
                $orderService = new OrderService(new Order, new Transection, new OrderedItem, new Product);
                $orderId = $orderService->saveOrder($data, $response, 'paypal');
                // $this->SaveOrder($data,$response, $tracking_id);
                
            }
        }
        return view('user.paypalResponse',compact('transectionId','type','tracking_id'));
        // return response()->json(['transectionId' => $response->id]);
    }


    public function SaveOrder($data, $response, $tracking_id)
    {
        $session_id = Session::getId();
        $json_data = array(
            'shipping_name' => $data->shipping_name,
            'shipping_email' => $data->shipping_email,
            'shipping_phone' => $data->shipping_phone,
            'shipping_company' => $data->shipping_company,
            'shipping_address' => $data->shipping_address,
            'shipping_town_city' => $data->shipping_town_city,
            'shipping_postal_code' => $data->shipping_postal_code,
        );
        $shipping_data = json_encode($json_data);
        $coupon_receive_data = \Cart::session(Session::getId())->getConditions()->last();
        $coupon_data = '';
        if($coupon_receive_data){
            $coupon_json_data = array(
                'name' => $coupon_receive_data->getName(),
                'type' => $coupon_receive_data->getType(),
                'value' => $coupon_receive_data->getValue(),
                'code' => $coupon_receive_data->getAttributes()['code'],
                'discount' => $coupon_receive_data->getAttributes()['discount'],
            );
            $coupon_data = json_encode($coupon_json_data);

            coupon::where('code', $coupon_receive_data->getAttributes()['code'])->update(['number_of_times' => $coupon_receive_data->getAttributes()['number_of_times'] - 1]);
        }

        $order = new Order();
        $order->order_type = "Ecommerce";
        $order->subtotal = \Cart::session(Session::getId())->getSubtotal();
        $order->order_quantity = \Cart::session(Session::getId())->getTotalQuantity();
        $order->total = $data->total;
        $order->user_id  = Auth::user()->id;
        $order->tracking_id  = $tracking_id;
        $order->invoice  = $data->invoice;
        $order->name  = $data->name;
        $order->email  = $data->email;
        $order->phone  = $data->phone;
        $order->company  = $data->company;
        $order->billing_address  = $data->billing_address;
        $order->billing_town  = $data->billing_town;
        $order->billing_postal_code  = $data->billing_postal_code;
        $order->shipping_data = $shipping_data;
        $order->coupon = $coupon_data;
        $order->shipping_title = $data->shipping_title;
        $order->shipping_cost = $data->shipping_cost;
        $order->payment_status  = "Paid";
        $order->order_status  = "Pending";
        $order->order_comments = $data->order_comments;
        $order->save();

        $transection = new Transection();
        $transection->user_id  = Auth::user()->id;
        $transection->order_id   = $order->id;
        $transection->transaction_number = $response['id'];
        $transection->paymentId = $response['payerId'];
        $transection->formable_type = "user";
        $transection->formable_id = Auth::user()->id;
        $transection->toable_type = "admin";
        $transection->toable_id = 1;
        $transection->payment_type = "Paypal";
        $transection->amount = $data->total;
        $transection->transection_time = date('Y-m-d H:i:s.u');
        
        $transection->save();

        $orderProducts = []; // Use a new variable to collect data for insert

        foreach (\Cart::session(Session::getId())->getContent() as $item) {
            $orderProducts[] = [
                'order_id' => $order->id,
                'product_id' => $item->id,
                'product_name' => $item->name,
                'product_price' => $item->price,
                'product_slug' => $item->attributes->slug,
                'product_SKU' => $item->attributes->SKU,
                'quantity' => $item->quantity,
                'product_attributes' => json_encode($item->attributes->attributes), // Encode JSON data if needed
                'product_attributes_value' => json_encode($item->attributes->attributes_value), // Encode JSON data if needed
                'product_refferer' => $item->attributes->refferer,
                'product_category' => $item->attributes->category,
                'product_subcategory' => $item->attributes->subcategory,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $product_fetch = Product::where('id',$item->id)->first();
            $stock = $product_fetch->stock - $item->quantity;
            $product_fetch->update(['stock' => $stock]);

        }

        OrderedItem::insert($orderProducts);

        \Cart::session($session_id)->clear();
        \Cart::session($session_id)->clearCartConditions();

        Mail::to($order->email)->later(now()->addMinutes(1), new OrderInvoiceMail($order,$transection));
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction()
    {
        $type = 'cancel';
        return view('user.paypalResponse',compact('type'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
