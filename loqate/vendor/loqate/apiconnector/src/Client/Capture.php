<?php

namespace Loqate\ApiConnector\Client;

use Loqate\ApiConnector\Client\Http\HttpClient;
use Loqate\ApiConnector\Utils\API;
use Throwable;

/**
 * Capture class
 */
class Capture
{
    /** @var string ApiKey */
    private $apiKey;

    /** @var HttpClient $httpClient */
    private $httpClient;

    /**
     * Capture constructor
     *
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->httpClient = new HttpClient();
        $this->apiKey = $apiKey;
    }

    /**
     * Search address using the find endpoint of the Loqate API
     *
     * @param $params
     * @return array|null
     */
    public function find($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('capture_find');

        try {
            $response = $this->httpClient->get($endpoint, $params);

            if (isset($response['Items']) && $items = $response['Items']) {
                $result = [];
                foreach ($items as $item) {
                    if ($item['Type'] === 'Container') {
                        $params['Container'] = $item['Id'];
                        $containerResponse = $this->httpClient->get($endpoint, $params);
                        if (isset($containerResponse['Items']) && $containerItems = $containerResponse['Items']) {
                            $result = array_merge($result, $containerItems);
                        }
                    } else {
                        $result[] = $item;
                    }
                }

                return $result;
            }
        } catch (Throwable $exception) {
            return ['error' => true, 'message' => $exception->getMessage()];
        }

        return ['error' => true, 'message' => 'Unexpected error occurred.'];
    }

    /**
     * Receive address details based on the ID received at find
     *
     * @param $params
     * @return array|mixed|null
     */
    public function retrieve($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('capture_retrieve');

        return $this->processResponse($endpoint, $params);
    }

    /**
     * Find address by latitude and longitude
     *
     * @param $params
     * @return array|mixed|null
     */
    public function geolocate($params)
    {
        $params['Key'] = $this->apiKey;
        $endpoint = API::getEndpoint('capture_geolocation');

        return $this->processResponse($endpoint, $params);
    }

    /**
     * Parse response for retrieve and geolocate methods
     *
     * @param $endpoint
     * @param $params
     * @return array|mixed
     */
    public function processResponse($endpoint, $params)
    {
        try {
            $response = $this->httpClient->get($endpoint, $params);

            if (isset($response['Items']) && $response['Items']) {
                if (isset($response['Items'][0]['Error'])) {
                    return ['error' => true, 'message' => $response['Items'][0]['Description']];
                }

                return $response['Items'];
            }

        } catch (Throwable $exception) {
            return ['error' => true, 'message' => $exception->getMessage()];
        }

        return ['error' => true, 'message' => 'Unexpected error occurred.'];
    }
}
