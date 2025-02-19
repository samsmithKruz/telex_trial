<?php



use App\Lib\Controller;
use App\Lib\Helpers;

class IntegrationController extends Controller
{
    public function __construct()
    {
        $this->model('Order');
    }
    /**
     * Loads the integration.json specification.
     *
     * This function returns the integration contents.
     * The parsed data is then used to configure the integration settings.
     *
     * @return array The parsed integration settings.
     */
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
    /**
     * This route returns list of orders for test purpose
     * @return never
     */
    public function listOrders()
    {
        $orders = $this->model->listOrder();
        jsonResponse($orders);
    }
    
    public function placeOrder()
    {
        $data = get_data();
        if (!Helpers::isMethod("POST")) {
            jsonResponse([
                'message' => "This route requires POST method to add seed",
                'status' => 'error'
            ], 405);
        }
        if (
            !isset($data['txn_id']) ||
            !isset($data['product_id']) ||
            !isset($data['description']) ||
            !isset($data['amount']) ||
            !isset($data['user_id'])
        ) {
            jsonResponse([
                'message' => "Payload to place order must contain [txn_id,product_id,description,amount,user_id]"
            ], 422);
        }
        if ($this->model->placeOrder($data)) {
            emit_event(
                event_name: "Placed Order",
                message: "An order has been placed for product: #" . $data['product_id'].", amount:".$data['amount'],
                status: 'success',
                username: 'order-placer'
            );
            jsonResponse(['message' => 'Order placed successfully'], 201);
        }
        emit_event(
            event_name: "Placed Order",
            message: "An error was encountered while trying place order for product: #" . $data['product_id'].", amount:".$data['amount'],
            status: 'error',
            username: 'order-placer'
        );
        jsonResponse(['message' => 'An error occurred while placing your order'], 500);
    }
    public function cancelOrder($params) {
        $order_id = @$params[0];
        if (!isset($order_id)) {
            jsonResponse([
                'message' => "You must pass order ID to cancel Order",
                'status' => 'error'
            ], 422);
        }
        if ($this->model->cancelOrder($order_id)) {
            emit_event(
                event_name: "Canceled Order",
                message: "An order has been canceled for order:{$order_id}",
                status: 'success',
                username: 'order-placer'
            );
            jsonResponse(['message' => 'Order cancelled successfully'], 201);
        }
        emit_event(
            event_name: "Placed Order",
            message: "An error while trying to cancel an order for order_id: {$order_id}",
            status: 'error',
            username: 'order-placer'
        );
        jsonResponse(['message' => 'An error occurred while cancelling your order'], 500);
    }
    public function deleteOrder($params) {
        $order_id = @$params[0];
        if (!isset($order_id)) {
            jsonResponse([
                'message' => "You must pass order ID to delete Order",
                'status' => 'error'
            ], 422);
        }
        if ($this->model->deleteOrder($order_id)) {
            emit_event(
                event_name: "Delete Order",
                message: "An order has been deleted for order:{$order_id}",
                status: 'success',
                username: 'order-placer'
            );
            jsonResponse(['message' => 'Order deleted successfully'], 201);
        }
        emit_event(
            event_name: "Delete Order",
            message: "An error while trying to delete an order for order_id: {$order_id}",
            status: 'error',
            username: 'order-placer'
        );
        jsonResponse(['message' => 'An error occurred while deleting your order'], 500);
    }
    public function processOrder($params) {
        $order_id = @$params[0];
        if (!isset($order_id)) {
            jsonResponse([
                'message' => "You must pass order ID to process Order",
                'status' => 'error'
            ], 422);
        }
        if ($this->model->processOrder($order_id)) {
            emit_event(
                event_name: "Process Order",
                message: "An order has been processed - order:{$order_id}",
                status: 'success',
                username: 'order-placer'
            );
            jsonResponse(['message' => 'Order processed successfully'], 201);
        }
        emit_event(
            event_name: "Process Order",
            message: "An error while trying to process an order for order_id: {$order_id}",
            status: 'error',
            username: 'order-placer'
        );
        jsonResponse(['message' => 'An error occurred while processing your order'], 500);
    }
    public function webhook()
    {
        $daily_orders = $this->model->getDailyOrderSummary();
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $event = emit_event(
            event_name: 'Daily Order Summarizer',
            message: "The order summary for {$yesterday} is:\n" . formatOutput($daily_orders, [
                'Order ID' => 'order_id',
                'Transaction ID' => 'txn_id',
                'Product ID' => 'product_id',
                'Description' => 'description',
                'Amount' => 'amount',
                'Status' => 'status',
                'User ID' => 'user_id',
                'Created At' => 'created_at'
            ]),
            status: 'success',
            username: 'order-notifier'
        );
        jsonResponse($event);
    }
    public function test_event(){
        $event = emit_event(
            event_name: 'Daily Order Summarizer',
            message: "This is to test and check the emitter works fine",
            status: 'success',
            username: 'order-notifier'
        );
        jsonResponse($event);

    }
}
