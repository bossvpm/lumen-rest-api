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

    private function validateLatLong($latLong)
    {
        if (preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $latLong[0]) && preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $latLong[1])) {
            return true;
        } else {
            return false;
        }
    }

}
