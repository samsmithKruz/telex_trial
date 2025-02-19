<?php

/**
 * Helper function to send json response to user and also payload
 * @param array $data
 * @param int $statusCode
 * @return never
 */
function jsonResponse($data, $statusCode = 200)
{
    header('content-type: application/json');
    http_response_code($statusCode);
    echo json_encode((array)$data);
    exit();
}
/**
 * Helper function for sending request and 
 * it utilizes on cUrl
 * @param string $url
 * @param string $method
 * @param array $data
 * @param array $headers
 * @return array
 */
function sendRequest($url, $method = 'GET', $data = [], $headers = [])
{
    $ch = curl_init();

    if (!empty($data) && $method === 'GET') {
        $url .= '?' . http_build_query($data);
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
    ];

    if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $error = curl_error($ch);

    curl_close($ch);

    return $error ? ['error' => $error] : json_decode($response, true);
}

/**
 * Helper function to write to log
 * @param string $message
 * @param string $file
 * @return void
 */
function logMessage($message, $file = 'logs.log')
{
    $logFile = __DIR__ . '/' . basename($file);
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
/**
 * Get payload sent
 * 
 * @return array returns the payload as an associative array
 */

function get_data()
{
    // Get the raw POST data from the request body
    $json_data = file_get_contents('php://input');

    // Decode the JSON data into a PHP associative array
    $data = json_decode($json_data, true);

    // Return the decoded data
    return $data;
}
/**
 * Emit an event to a specified Telex channel.
 *
 * @param string $event_name The name of the event to represent the event.
 * @param string $message The body of the message to show in Telex.
 * @param string $username The username of the pusher to identify the event in Telex.
 * @param string $hook_url The URL of the Telex channel to send the event to.
 * @param string $status This indicates the status of event to emit 'success|error'
 */
function emit_event($event_name, $message, $status, $username, $hook_url = false)
{
    $hook_url = $hook_url ? "":getenv('WEBHOOK_URL');
    $payload = [
        "event_name" => $event_name,
        "message" => $message,
        "status" => $status,
        "username" => $username
    ];

    return sendRequest($hook_url ?: "", "POST", $payload, ['content-type: application/json']);
}
/**
 * Formats the given input string by applying the specified format options.
 *
 * @param string $input The input string to be formatted.
 * @param array $options An associative array of format options. Supported options include:
 *                       - 'uppercase' (bool): If true, converts the string to uppercase.
 *                       - 'lowercase' (bool): If true, converts the string to lowercase.
 *                       - 'capitalize' (bool): If true, capitalizes the first letter of each word.
 *                       - 'trim' (bool): If true, trims whitespace from the beginning and end of the string.
 *                       - 'prefix' (string): A string to prepend to the input string.
 *                       - 'suffix' (string): A string to append to the input string.
 *
 * @return string The formatted string.
 */
function formatOutput($data, $headings)
{
    $data = (array)$data;
    $output = "";
    foreach ($data as $item) {
        foreach ($headings as $heading => $key) {
            $output .= "$heading: {$item[$key]}\n";
        }
        $output .= "-------------------------\n";
    }
    return $output;
}
