<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        // Get data from DataProviderX.json
        $dataProviderXPath = public_path('data/DataProviderX.json');
        $dataProviderXData = json_decode(file_get_contents($dataProviderXPath), true);

        // Get data from DataProviderY.json
        $dataProviderYPath = public_path('data/DataProviderY.json');
        $dataProviderYData = json_decode(file_get_contents($dataProviderYPath), true);

        // Combine data from both providers
        $combinedData = [
            'DataProviderX' => $dataProviderXData,
            'DataProviderY' => $dataProviderYData,
        ];

        // Apply filters based on query parameters
        if ($request->has('provider')) {
            $providerFilter = $request->input('provider');

            // Check if the providerFilter is a valid key in the combined data
            if (array_key_exists($providerFilter, $combinedData)) {
                // Return the filtered result for the specified provider
                return response()->json($combinedData[$providerFilter]);
            } else {
                // Return an empty array if the specified provider is not found
                return response()->json([]);
            }
        }

        // Filter by 'statusName' if the parameter is provided
        if ($request->has('statusName')) {
            $statusNameFilter = strtolower($request->input('statusName'));

            // Use array_filter to filter data based on 'statusName'
            $filteredData = array_filter($combinedData, function ($item) use ($statusNameFilter) {
                // Check if the 'statusCode' key exists
                if (isset($item['statusCode'])) {
                    $status = $item['statusCode'];
                } elseif (isset($item['status'])) {
                    $status = $item['status'];
                } else {
                    return false; // Handle the case where neither 'statusCode' nor 'status' is present
                }

                // Map numeric status codes to status names
                $statusMap = [
                    1 => 'authorised',
                    2 => 'decline',
                    3 => 'refunded',
                    100 => 'authorised',
                    200 => 'decline',
                    300 => 'refunded',
                ];

                $statusName = $statusMap[$status] ?? 'unknown';
                return strtolower($statusName) == $statusNameFilter;
            });

            // Update combinedData with the filtered result
            $combinedData = $filteredData;
        }

        // Filter by 'statusCode' if the parameter is provided
        if ($request->has('statusCode')) {
            $statusCodeFilter = $request->input('statusCode');

            // Use array_filter to filter data based on 'statusCode'
            $filteredData = array_filter($combinedData, function ($item) use ($statusCodeFilter) {
                // Check if the 'statusCode' key exists
                if (isset($item['statusCode'])) {
                    return $item['statusCode'] == $statusCodeFilter;
                }
                return false; // Handle the case where 'statusCode' is not present
            });

            // Update combinedData with the filtered result
            $combinedData = $filteredData;
        }

        // Filter by amount range if 'balanceMin' and 'balanceMax' parameters are provided
        if ($request->has('balanceMin') && $request->has('balanceMax')) {
            $balanceMin = $request->input('balanceMin');
            $balanceMax = $request->input('balanceMax');

            // Use array_filter to filter data based on the amount range
            $filteredData = array_filter($combinedData, function ($item) use ($balanceMin, $balanceMax) {
                // Check if the 'parentAmount' key exists in DataProviderX
                if (isset($item['DataProviderX']['parentAmount'])) {
                    $parentAmount = $item['DataProviderX']['parentAmount'];
                    return $parentAmount >= $balanceMin && $parentAmount <= $balanceMax;
                }

                // Check if the 'balance' key exists in DataProviderY
                if (isset($item['DataProviderY']['balance'])) {
                    $balance = $item['DataProviderY']['balance'];
                    return $balance >= $balanceMin && $balance <= $balanceMax;
                }

                return false; // Handle the case where neither 'parentAmount' nor 'balance' is present
            });

            // Update combinedData with the filtered result
            $combinedData = $filteredData;
        }

        // Filter by 'currency' if the parameter is provided
        if ($request->has('currency')) {
            $currencyFilter = $request->input('currency');

            // Use array_filter to filter data based on 'currency'
            $filteredData = array_filter($combinedData, function ($item) use ($currencyFilter) {
                // Check if the 'Currency' key exists in DataProviderX
                if (isset($item['DataProviderX']['Currency'])) {
                    $currency = $item['DataProviderX']['Currency'];
                    return strtolower($currency) == strtolower($currencyFilter);
                }

                // Check if the 'currency' key exists in DataProviderY
                if (isset($item['DataProviderY']['currency'])) {
                    $currency = $item['DataProviderY']['currency'];
                    return strtolower($currency) == strtolower($currencyFilter);
                }

                return false; // Handle the case where neither 'Currency' nor 'currency' is present
            });

            // Update combinedData with the filtered result
            $combinedData = $filteredData;
        }

        // Return the unfiltered result if no provider or statusName or statusCode or balance range or currency filter is applied
        return response()->json($combinedData);
    }


}
