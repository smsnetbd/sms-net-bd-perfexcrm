<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Sms_smsapi extends App_sms
{

    private $apikey;
    private $from;
    private $testsms;
    private $save_messagess;
    private $balance;
    private $sender_ids;
    private $api_url;
    

    public function __construct()
    {
        parent::__construct();

        $this->apikey = $this->get_option("smsapi", "apikey");

        $this->from = $this->get_option("smsapi", "from");

        $this->testsms = $this->get_option("smsapi", "testsms");

        $this->save_messagess = $this->get_option("smsapi", "save_messagess");

        update_option('sms_smsapi_active', '1');

        $this->api_url = api_url();

        $this->balance = get_balance($this->apikey);
        
        $this->sender_ids = get_sender_ids($this->apikey);
        
        $save_messagess_page = $this->save_messagess ? '<div><a class="btn btn-sm btn-warning" href="' . admin_url(ALPHASMS_MODULE_NAME) . '"><i class="fa-list-alt fa-regular tw-mr-1"></i>' . _l('smsapi_log2') . '</a></div>' : '';


        $options = [

            [
                "name" => "apikey",
                "label" => _l("smsapi_apikey_label"),
                "info" => _l('smsapi_apikey_info') . "<hr class=\"hr-15\" />",
            ],

            [
                'name'          => 'save_messagess',
                'field_type'    => 'radio',
                'default_value' => '1',
                'label'         => _l("smsapi_save_messagess_label"),
                "info" => "<p>" . _l("smsapi_save_messagess_info", $save_messagess_page) . "</p><hr class=\"hr-15\" />",
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
                "info" => "",
                'options'       => [
                    ['label' => _l('settings_yes'), 'value' => 1],
                    ['label' => _l('settings_no'), 'value' => 0],
                ],
            ],

            [
                'name'          => 'balance',
                'field_type'    => 'info',
                "info" =>   _l("smsapi_balance_label") . ": " . $this->balance . "</p><hr class=\"hr-15\" />",
            ],

        ];

        if ($this->sender_ids) {
            $options[] = [
                'name'          => 'from',
                'field_type'    => 'radio',
                'default_value' => 'default',
                'label'         => _l("smsapi_sender_id_label"),
                "info"          => "<hr class=\"hr-15\" />",
                'options'       => array_merge(
                    [
                        [
                            'label'   => _l('no_sender_id'), // Default option
                            'value'   => 'default',
                            'checked' => true // Ensuring default is selected
                        ]
                    ],
                    array_map(function ($sender) {
                        return [
                            'label' => $sender['sender_id'],
                            'value' => $sender['sender_id']
                        ];
                    }, $this->sender_ids)
                ),
            ];
        }else{
            $options[] = [
                'name'          => 'from',
                'field_type'    => 'radio',
                'default_value' => 'default',
                'label'         => _l("smsapi_sender_id_label"),
                "info"          => "<hr class=\"hr-15\" />",
                "options" => [
                    [
                        'label'   => _l('no_sender_id'), // Default option
                        'value'   => 'default',
                        'checked' => true // Ensuring default is selected
                    ]
                ],
            ];
        }
        

        $this->add_gateway("smsapi", [
            "name" => "SMSAPI",
            "options" => $options,
        ]);
    }

    public function send($number, $message)
    {
        try {
            $sendData = [
                'api_key' => $this->apikey,
                'msg' => $message,
                'to' => $number,
            ];
    
            if ($this->from && in_array($this->from, array_column($this->sender_ids, 'sender_id'))) {
                $sendData['sender_id'] = $this->from;
            }
    
            if ($this->save_messagess) {
                $sms_hash = app_generate_hash();
                $sendData['notify_url'] = site_url("smsapi/reports/{$sms_hash}");
            }
    
            $response = $this->client->request('POST', $this->api_url . 'sendsms', [
                'form_params' => $sendData,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'http_errors' => false
            ]);
    
            $statusCode = $response->getStatusCode();
            $send = json_decode($response->getBody()->getContents(), true);
            $api_log = json_encode($send, JSON_UNESCAPED_UNICODE);
    
            if ($this->save_messagess) {

                $CI = &get_instance();

                $CI->load->model(ALPHASMS_MODULE_NAME . '/smsapi_model');

               $send['msg'] = (isset($send['error']) && $send['error'] == 0) ? null :  $send['msg'];

               $this->saveMessageLog($CI->smsapi_model, $sms_hash, $number, $message, $send);

            }
    

            if ($statusCode == 200 && isset($send["error"]) && $send["error"] == 0) {
                $this->logSuccess($number, "{$message}, API_LOG:{$api_log}, StatusCode:{$statusCode}");
                return true;
            } else {
                $this->set_error("Message:{$message}, To:{$number}, API_LOG:{$api_log}, StatusCode:{$statusCode}");
                return false;
            }


        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $this->set_error("HTTP Request Failed: " . $e->getMessage());
            return false;

        } catch (Exception $e) {

            $this->set_error("Unexpected Error: " . $e->getMessage());
            return false;

        }
    }
    
    private function saveMessageLog($smsapi_model, $sms_hash, $number, $message, $send)
    {
        $smsapi_model->add('sms', [
            'hash' => $sms_hash,
            'testsms' => $this->testsms,
            'sms_from' => $this->from ?? null,
            'sms_to' => $number,
            'sms_message' => $message,
            'error' => $send['error'] ?? null,
            'error_message' => $send['msg'] ?? null,
            'request_id' => $send['data']['request_id'] ?? null,
        ]);
    }
    


}
