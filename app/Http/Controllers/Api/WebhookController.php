<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Error logging
        ini_set('log_errors', 1);
        ini_set('error_log', storage_path('logs/StroAccountWebHook_error.log'));

        // Get the input data
        $input = $request->getContent();

        // Write the data to a file
        $file = fopen(storage_path('logs/StroAccountWebHook.log'), 'w');
        if ($file !== false) {
            fwrite($file, $input);
            fclose($file);
        } else {
            Log::error("Failed to open StroAccountWebHook.log for writing.");
        }

        // Parse the JSON input
        $response = json_decode($input, true);

        // Process the webhook data
        if (is_array($response) && isset($response["sessionId"])) {
            // Extracting fields from the webhook payload
            $sessionId = $response["sessionId"];
            $accountNumber = $response["accountNumber"];
            $tranRemarks = $response["tranRemarks"];
            $transactionAmount = $response["transactionAmount"];
            $settledAmount = $response["settledAmount"];
            $feeAmount = $response["feeAmount"];
            $vatAmount = $response["vatAmount"];
            $currency = $response["currency"];
            $initiationTranRef = $response["initiationTranRef"];
            $settlementId = $response["settlementId"];
            $sourceAccountNumber = $response["sourceAccountNumber"];
            $sourceAccountName = $response["sourceAccountName"];
            $sourceBankName = $response["sourceBankName"];
            $channelId = $response["channelId"];
            $tranDateTime = $response["tranDateTime"];

            // Further processing if needed

            // Return a success response
            return response()->json(['message' => 'Webhook processed successfully'], 200);
        } else {
            Log::error("Webhook data was not received or lacked the sessionId field.");
            return response()->json(['message' => 'Webhook data was not received or lacked the sessionId field.'], 400);
        }
    }
}
