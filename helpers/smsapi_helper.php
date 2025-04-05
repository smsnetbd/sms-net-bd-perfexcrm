<?php defined('BASEPATH') or exit('No direct script access allowed');

function is_smsapi_save_messages()

{
    $CI = &get_instance();
    return class_exists(Sms_smsapi::class) && $CI->sms_smsapi->get_option("smsapi", "save_messagess");
}


function get_balance($apikey = "")
{

    if (empty($apikey)) {
        return "API key is required";
    }
    
    $data = make_api_request("/user/balance/", $apikey);

    // Check if balance exists in the response
    if (isset($data['data']['balance'])) {
        return  formatBDT($data['data']['balance']);
    }

    // Handle API errors
    return isset($data['error']) && $data['error'] != 0 ? ($data['msg'] ?? "Invalid API Key") : "Unknown error.";
}

function get_sender_ids($apikey = "")
{
    $data = make_api_request("/config/senderid/", $apikey);

    // Handle API errors
    return $data['data']['items'] ?? [];
}

function formatBDT($amount)
{
    $amount = number_format($amount, 2, '.', ',');
    $parts = explode('.', $amount); // Split integer and decimal parts

    $integerPart = $parts[0];
    $decimalPart = isset($parts[1]) ? $parts[1] : '00';

    // Apply Bangladeshi comma formatting
    $integerPart = preg_replace('/\B(?=(\d{2}){1,3}\b)/', ',', $integerPart);

    return $integerPart . '.' . $decimalPart . ' BDT'; // Append "BDT"
}

function api_url()
{
    return "http://api.dev.alpha.net.bd/";
}

 
function make_api_request($endpoint, $apikey)
{

    $api_url = api_url();
    // Ensure the API URL is set correctly
    if (!empty($api_url)) {

        $apiUrl = $api_url . $endpoint;

        $params = [
            "api_key" => $apikey
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,  // Ignore SSL verification (not recommended for production)
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false) {
            return "cURL Error: $curlError";
        }

        // Decode JSON response
        $data = json_decode($response, true);

        // Handle invalid JSON response
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Invalid response format from API.";
        }

        // Handle HTTP errors (e.g., 404 Not Found)
        if ($httpCode !== 200) {
            return "Error: HTTP $httpCode - " . ($data['msg'] ?? 'Unknown error');
        }

        return $data;
    }

    return false;
}
