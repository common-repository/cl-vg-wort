<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest;

use Exception;

/**
 * Class Client
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class Client
{
    const RESULT_STATUS_NOT_FOUND = 'NOT_FOUND';

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @param string $_apiKey
     */
    public function __construct(string $_apiKey)
    {
        $this->apiKey = $_apiKey;
    }

    /**
     * Function sendRequest
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Rest\Request $_request
     *
     * @return int[]
     * @throws \Exception
     */
    public function sendRequest(Request $_request): array
    {
        $data = $_request->data();

        $requestData = [
            'method' => 'GET',
            'headers' => $this->prepareHeader($_request),
            'httpversion' => '1.0',
            'sslverify' => false,
            'timeout' => 30,
        ];

        if (!empty($data)) {
            $requestData['method'] = 'POST';
            $requestData['body'] = json_encode($data, JSON_UNESCAPED_SLASHES);
        }

        $response = wp_remote_request($_request::ENDPOINT, $requestData);

        if (!is_array($response)) {
            throw new Exception($response->get_error_message());
        }

        if ($response['body'] === '') {
            return [
                'status' => wp_remote_retrieve_response_code( $response )
            ];
        }

        return $_request->handleResponse(json_decode($response['body'], true));
    }

    /**
     * Function prepareHeader
     *
     * @return string[]
     *
     * @noinspection PhpUnusedParameterInspection
     */
    private function prepareHeader(Request $_request): array
    {
        return [
            'Content-Type' => 'application/json',
            'api_key' => $this->apiKey,
        ];
    }
}
