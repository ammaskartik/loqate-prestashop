<?php

namespace Loqate\ApiConnector\Client;

use Loqate\ApiConnector\Client\Http\HttpClient;
use Loqate\ApiConnector\Utils\API;
use Throwable;

/**
 * Verify class
 */
class Verify
{
    /** @var string ApiKey */
    private $apiKey;

    /** @var HttpClient $httpClient */
    private $httpClient;

    /**
     * Verify constructor
     *
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->httpClient = new HttpClient();
        $this->apiKey = $apiKey;
    }

    /**
     * Verify address using the address_verify endpoint of the Loqate API
     *
     * @param $params
     * @return array
     */
    public function verifyAddress($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('address_verify');

        try {
            $response = $this->httpClient->post($endpoint, $params);

            if (is_array($response)) {
                return array_column($response, 'Matches');
            }
        } catch (Throwable $exception) {
            return ['error' => true, 'message' => $exception->getMessage()];
        }

        return ['error' => true, 'message' => 'Unexpected error occurred.'];
    }

    /**
     * Verify email address
     *
     * @param $params
     * @return array|bool
     */
    public function verifyEmail($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('email_verification');

        try {
            $response = $this->httpClient->get($endpoint, $params);

            if (isset($response['Items']) && $item = reset($response['Items'])) {
                return $item['ResponseCode'] === 'Valid';
            }
        } catch (Throwable $exception) {
            return ['error' => true, 'message' => $exception->getMessage()];
        }

        return ['error' => true, 'message' => 'Unexpected error occurred.'];
    }

    /**
     * Verify phone number
     *
     * @param $params
     * @return array|bool
     */
    public function verifyPhone($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('phone_verification');

        try {
            $response = $this->httpClient->get($endpoint, $params);

            if (isset($response['Items']) && $item = reset($response['Items'])) {
                return $item['IsValid'] === 'Yes';
            }
        } catch (Throwable $exception) {
            return ['error' => true, 'message' => $exception->getMessage()];
        }

        return ['error' => true, 'message' => 'Unexpected error occurred.'];
    }
}
