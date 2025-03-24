<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Sms_smsapi extends App_sms
{

    private $apikey;
	private $from;
	private $testsms;
	private $save_messagess;
    private $api_url = "http://api.dev.alpha.net.bd/";

    public function __construct()
    {
        parent::__construct();
        
        $this->apikey = $this->get_option("smsapi", "apikey");
        $this->from = $this->get_option("smsapi", "from");
        $this->testsms = $this->get_option("smsapi", "testsms");
        $this->save_messagess = $this->get_option("smsapi", "save_messagess");

        $save_messagess_page = $this->save_messagess ? '<div><a class="btn btn-sm btn-warning" href="'.admin_url(ALPHASMS_MODULE_NAME).'"><i class="fa-list-alt fa-regular tw-mr-1"></i>'._l('smsapi_log2').'</a></div>' : '';

        $senderIds = Sms_smsapi::get_sender_ids();

        $options = [
            [
                "name" => "apikey",
                "label" => _l("smsapi_apikey_label"),
                "info" => _l('smsapi_apikey_info') . "<hr class=\"hr-15\" />",
            ],

                        [
                'name'          => 'save_messagess',
                'field_type'    => 'radio',
                'default_value' => '0',
                'label'         => _l("smsapi_save_messagess_label"),
                "info" => "<p>"._l("smsapi_save_messagess_info",$save_messagess_page)."</p><hr class=\"hr-15\" />",
                'options'       => [
                    ['label' => _l('settings_yes'), 'value' => 1],
                    ['label' => _l('settings_no'), 'value' => 0],
                ],
            ],

            [
                'name'          => 'testsms',
                'field_type'    => 'radio',
                'default_value' => '0',
                'label'         => _l("smsapi_testsms_label"),
                "info" => "<p>"._l("smsapi_testsms_info")."</p><hr class=\"hr-15\" />",
                'options'       => [
                    ['label' => _l('settings_yes'), 'value' => 1],
                    ['label' => _l('settings_no'), 'value' => 0],
                ],
            ],

            [
                'field_type'    => 'info',
                "info" =>   _l("smsapi_balance_label").": ". Sms_smsapi::get_balance()."</p><hr class=\"hr-15\" />",
            ],
        ];

        if ($senderIds) {
            $options[] = [
                'name'          => 'from',
                'field_type'    => 'radio',
                'default_value' => 'default',
                'label'         => _l("smsapi_sender_id_label"),
                "info"          => "<hr class=\"hr-15\" />",
                'options'       => array_merge(
                    [
                        [
                            'label' => _l('no_sender_id'), // Default option
                            'value' => 'no_sender_id',
                        ]
                    ],
                    array_map(function ($sender) {
                        return [
                            'label' => $sender['sender_id'],
                            'value' => $sender['sender_id']
                        ];
                    }, $senderIds)
                ),
            ];
        }
     

        $this->add_gateway("smsapi", [
            "name" => "SMSAPI",
            "options" => $options,
        ]);
    }

    public function send($number, $message)
    {

        $file_path = FCPATH . '/uploads/client_logs.txt';

        // Prepare content
        $content = "new message send ". date("Y-m-d H:i:s") . "\n";

        // Write to file
        file_put_contents($file_path, $content, FILE_APPEND | LOCK_EX);



        try {

            $sendData = [
                'api_key' => $this->apikey,
                'msg' => $message,
                'to' => $number,
            ];

            // Sender Id check in database
            if ($this->from && in_array($this->from, array_column($this->get_sender_ids(), 'sender_id'))) {
                $sendData['sender_id'] = $this->from;
            }

            if ($this->save_messagess) {
                $sms_hash = app_generate_hash();
                $sendData['notify_url'] = site_url("smsapi/reports/{$sms_hash}");
            }

            $response = $this->client->request(
                'POST', $this->api_url.'sendsms', [
                'form_params' => $sendData,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'http_errors' => false
            ]);

            $send = json_decode($response->getBody()->getContents(), true);
            
            $statusCode = $response->getStatusCode();

            if( isset($send["error"]) ){
                $send['error_message'] = _l("smsapi_errorcode_{$send["error"]}");
            }

            if( isset($send['count']) && $send['count'] > 0 && isset($send['list']) ){
                foreach( $send['list'] as &$item ){
                    $item['status_message'] = _l("smsapi_status_{$item['status']}");
                }
            }

            $api_log = json_encode( $send, JSON_UNESCAPED_UNICODE );

            if( $this->save_messagess ){
                $CI = &get_instance();
                $CI->load->model(ALPHASMS_MODULE_NAME.'/smsapi_model');
                $addData = [];
            }

            if ($statusCode == 200 ) {
                if( !isset($send["error"]) && isset($send['count']) && $send['count'] > 0 ){
                    if( $this->save_messagess ){
                        foreach( $send['list'] as $item ){
                            $CI->smsapi_model->add('sms', [
                                'hash' => $sms_hash,
                                'testsms' => $this->testsms,
                                'sms_to' => $number,
                                'sms_from' => $this->from ?? null,
                                'sms_message' => $message,
                                'error' => $send['error'] ?? null,
                                'ms_id' => $send['data']['request_id'] ?? null,
                                'ms_points' => $item['points'],
                                'ms_number' => $item['number'],
                                'ms_date_sent' => $item['date_sent'],
                                'ms_submitted_number' => $item['submitted_number'],
                                'ms_status' => $item['status'],
                            ]);
                        }
                    }
                    $this->logSuccess($number, "{$message}, API_LOG:{$api_log}, StatusCode:{$statusCode}");
                    return true;
                }else{

                    if( $this->save_messagess ){
                        $CI->smsapi_model->add('sms', [
                            'hash' => $sms_hash,
                            'testsms' => $this->testsms,
                            'sms_to' => $number,
                            'sms_from' => $this->from ?? null,
                            'sms_message' => $message,
                            'error' => $send['error'] ?? null,
                            'error_message' => $send['message'] ?? null,
                            'ms_id' => $send['data']['request_id'] ?? null,
                            'error_invalid_numbers' => $send['invalid_numbers'] ?? null
                        ]);
                    }

                    $this->set_error("Message:{$message}, To:{$number}, API_LOG:{$api_log}, StatusCode:{$statusCode}");
                    return false;
                }
                
            } else {
                $this->set_error("Message:{$message}, To:{$number}, API_LOG:{$api_log}, StatusCode:{$statusCode}");
                return false;
            }


        } catch (Exception $e) {
            $this->set_error($e->getMessage());
            return false;
        }
    }


    public function get_balance() {
        $data = $this->make_api_request("/user/balance/");
    
        // If the response indicates an error, return the error message
        if (is_string($data)) {
            return $data;
        }
    
        // Check if balance exists in the response
        if (isset($data['data']['balance'])) {
            return  $this->formatBDT($data['data']['balance']);
        }
    
        // Handle API errors
        return isset($data['error']) && $data['error'] != 0 ? ($data['msg'] ?? "Invalid API Key") : "Unknown error.";
    }
    
    public function get_sender_ids() {
        $data = $this->make_api_request("/config/senderid/");
    
        // If the response indicates an error, return the error message
        if (is_string($data)) {
            return $data;
        }
    
        // Handle API errors
        return $data['data']['items'] ?? "No sender IDs available.";
    }

    private function formatBDT($amount) {
        $amount = number_format($amount, 2, '.', ','); 
        $parts = explode('.', $amount); // Split integer and decimal parts
    
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '00';
    
        // Apply Bangladeshi comma formatting
        $integerPart = preg_replace('/\B(?=(\d{2}){1,3}\b)/', ',', $integerPart);
    
        return $integerPart . '.' . $decimalPart . ' BDT'; // Append "BDT"
    }

    private function make_api_request($endpoint) {
        // Ensure the API URL is set correctly
        if (!isset($this->api_url)) {
            return "Invalid API service selected.";
        }
    
        $apiUrl = $this->api_url . $endpoint;
    
        $params = [
            "api_key" => $this->apikey
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
    
}