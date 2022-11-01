<?php

namespace Loqate\ApiConnector\Client\Http;

use Exception;

/**
 * HttpClient class
 */
class HttpClient
{
    /**
     * Do GET request
     *
     * @throws Exception
     */
    public function get(string $endpoint, array $params)
    {
        $queryString = http_build_query($params);
        $endpoint .= '?' . $queryString;

        $cURLConnection = curl_init();
        curl_setopt($cURLConnection, CURLOPT_URL, $endpoint);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($cURLConnection);
        $response = json_decode($response, true);
        curl_close($cURLConnection);

        if ($errorMessage = $this->searchForError($response)) {
            throw new Exception($errorMessage);
        }

        return $response;
    }

    /**
     * Do POST request
     *
     * @throws Exception
     */
    public function post(string $endpoint, array $params)
    {
        $cURLConnection = curl_init($endpoint);
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($cURLConnection);
        $response = json_decode($response, true);
        curl_close($cURLConnection);

        if ($errorMessage = $this->searchForError($response)) {
            throw new Exception($errorMessage);
        }

        return $response;
    }

    /**
     * Check for error in response
     *
     * @param $response
     * @return false|mixed
     */
    private function searchForError($response)
    {
        if (isset($response['Items'][0]['Error'])) {
            return $response['Items'][0]['Description'];
        }

        if (isset($response['Number'])) {
            return $response['Description'];
        }

        return false;
    }
}
