<?php

namespace Loqate\ApiConnector\Utils;

/**
 * API Utils class
 */
class API
{
    /** @var string BASE_URL */
    const BASE_URL = 'https://api.addressy.com';

    /** @var string[] ENDPOINTS */
    const ENDPOINTS = [
        'capture_find' => '/Capture/Interactive/Find/v1.10/json3.ws',
        'capture_retrieve' => '/Capture/Interactive/Retrieve/v1.20/json3.ws',
        'capture_geolocation' => '/Capture/Interactive/GeoLocation/v1.00/json3.ws',
        'address_verify' => '/Cleansing/International/Batch/v1.00/json4.ws',
        'email_verification' => '/EmailValidation/Interactive/Validate/v2.00/json3.ws',
        'phone_verification' => '/PhoneNumberValidation/Interactive/Validate/v2.20/json3.ws'
    ];

    /**
     * Get the list of loqate API endpoints
     *
     * @return string[]
     */
    public static function getAllEndpoints(): array
    {
        return self::ENDPOINTS;
    }

    /**
     * Get API endpoint by specific key
     *
     * @param $key
     * @return string|null
     */
    public static function getEndpoint($key): ?string
    {
        if (!isset(self::ENDPOINTS[$key])) {
            return null;
        }

        return self::BASE_URL . self::ENDPOINTS[$key];
    }
}
