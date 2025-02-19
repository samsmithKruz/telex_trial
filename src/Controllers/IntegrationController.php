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
                "integration_category" => "E-commerce & Retail",
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
                "tick_url" => "https://space.otecfx.com/integration/webhook",
                "target_url" => "https://space.otecfx.com/"
            ],
        ]);
    }
    public function webhook()
    {
        $data = Helpers::isMethod("POST") ? get_data() : [];
        logMessage(implode("|",$data));
        $event = emit_event(
            event_name: 'Order Notification',
            message: 'Order of #w89f8 was made for $35.23',
            status: 'failed',
            username: 'order-notifier'
        );
        jsonResponse($event);
    }
}
