<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function placeOrder(Request $request)
    {
        $data = $request->json()->all();
        if (!empty($data['origin']) && !empty($data['origin']) && $this->validateLatLong($data['origin']) && $this->validateLatLong($data['destination'])) {

            $order = new Order;
            $order->origin = implode('|', $data['origin']);
            $order->destination = implode('|', $data['destination']);
            $order->status = "UNASSIGN";
            $order->distance = "NA";
            $client = new \GuzzleHttp\Client();
            $googleApiResponse = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $order->origin . '&destinations=' . $order->destination . '&key=' . $_ENV['GOOGLE_MAPS_KEY']);
            if ($googleApiResponse->getStatusCode() == '200') {
                $googleApiResponse = json_decode($googleApiResponse->getBody());
                if ($googleApiResponse->status == "OK" && !empty($googleApiResponse->rows[0]->elements[0]->distance->text)) {
                    $order->distance = $googleApiResponse->rows[0]->elements[0]->distance->text;
                }
            }
            if ($order->save()) {
                unset($order->origin, $order->destination, $order->created_at, $order->updated_at);
                return response()->json($order);
            } else {
                $response = array("error" => "Unable to create order. Please try again later.");
                return response()->json($response, 500);
            }
        } else {
            $response = array("error" => "Invalid request. Please verify input data.");
            return response()->json($response, 500);
        }
    }

    public function takeOrder(Request $request, $id)
    {
        $data = $request->json()->all();
        if (is_numeric($id) && $data['status'] == 'taken') {
            $order = Order::find($id);
            if ($order->status != "taken") {
                $order->status = "taken";
                if ($order->save()) {
                    $response = array("status" => "SUCCESS");
                    return response()->json($response);
                } else {
                    $response = array("error" => "Unable to take order. Please try again later.");
                    return response()->json($response, 500);
                }
            } else {
                $response = array("error" => "ORDER_ALREADY_BEEN_TAKEN");
                return response()->json($response, 409);
            }
        } else {
            $response = array("error" => "Invalid request. Please verify input data.");
            return response()->json($response, 500);
        }
    }

    public function getOrders(Request $request)
    {
        $offset = $request->input("page");
        $limit = $request->input("limit");
        if (is_numeric($offset) && is_numeric($limit)) {
            $orders = Order::take($limit)
                ->skip($offset)
                ->select('id', 'distance', 'status')
                ->get();
            return response()->json($orders);
        } else {
            $response = array("error" => "Invalid request. Please verify input data.");
            return response()->json($response, 500);
        }
    }

    private function validateLatLong($latLong)
    {
        if (preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $latLong[0]) && preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $latLong[1])) {
            return true;
        } else {
            return false;
        }
    }

}
