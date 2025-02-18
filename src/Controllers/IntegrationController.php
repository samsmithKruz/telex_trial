<?php

use App\Lib\Controller;
use App\Lib\Helpers;

class IntegrationController extends Controller
{
    public function index()
    {

        jsonResponse([
            "data" => [
                "date" => [
                    "created_at" => "2025-02-18",
                    "updated_at" => "2025-02-18"
                ],
                "descriptions" => [
                    "app_name" => "Telex Trial",
                    "app_description" => "Sends notifications to a Telex channel whenever a new order is placed on the platform",
                    "app_logo" => "https://miro.medium.com/v2/resize:fit:1400/1*0KFB17_NGTPB0XWyc4BSgQ.jpeg",
                    "app_url" => "https://space.otecfx.com",
                    "background_color" => "#fff"
                ],
                "is_active" => true,
                "integration_type" => "interval",
                "integration_category"=>"E-commerce & Retail",
                "key_features" => [
                    "Order notifier",
                    "Order Monitor"
                ],
                "permissions" => [
                    "monitoring_user" => [
                        "always_online" => true,
                        "display_name" => "Order Monitor"
                    ]
                ],
                "author" => "Samuel Benny",
                "settings" => [
                    [
                        "label" => "interval",
                        "type" => "text",
                        "required" => true,
                        "default" => "* * * * *"
                    ],
                    [
                        "label" => "Key",
                        "type" => "text",
                        "required" => true,
                        "default" => "1234567890"
                    ],
                    [
                        "label" => "Do you want to continue",
                        "type" => "checkbox",
                        "required" => true,
                        "default" => "Yes"
                    ],
                    [
                        "label" => "Provide Speed",
                        "type" => "number",
                        "required" => true,
                        "default" => "1000"
                    ],
                    [
                        "label" => "Sensitivity Level",
                        "type" => "dropdown",
                        "required" => true,
                        "default" => "Low",
                        "options" => ["High", "Low"]
                    ],
                    [
                        "label" => "Alert Admin",
                        "type" => "multi-checkbox",
                        "required" => true,
                        "default" => "Super-Admin",
                        "options" => ["Super-Admin", "Admin", "Manager", "Developer"]
                    ]
                ],
                "tick_url" => "https://space.otecfx.com/webhook"
            ],
        ]);
    }
    public function webhook()
    {
        if(Helpers::isMethod("POST")){
            $data = get_data();
            logMessage($data);
        }
        // $url = "https://ping.telex.im/v1/webhooks/01951a3c-6514-780b-b2e1-ab4391045e0f";
        $url = "https://ping.telex.im/v1/webhooks/01951a96-68a2-7823-8dbd-b76419fb741b";
        $data = array(
            "event_name" => "string",
            "message" => "php post",
            "status" => "success",
            "username" => "collins"
        );
        $response = sendRequest($url, 'POST', $data);
        jsonResponse($response);
    }
}
