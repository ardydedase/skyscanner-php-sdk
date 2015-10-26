<?php

namespace Skyscanner\Utils;

use Skyscanner\Transport\Transport;
use Skyscanner\Exceptions\BadJSONException;

/**
 * Class NetworkUtils
 * @package Skyscanner\Utils
 * @author * *
 */
class NetworkUtils
{
    /**
     * In case HttpRequest is not available.
     * Use the native PHP Curl
     */
    public static function httpRequest(
        $serviceUrl,
        $headers = null,
        $method = Transport::GET,
        $data = null) {

        echo "\nserviceURL: $serviceUrl\n";
        echo "\nmethod: $method\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method == Transport::POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $resp = curl_exec($ch);

        curl_close($ch);

        return $resp;
    }

    public static function getJSONStr($jsonString)
    {
        // $jsonString = preg_replace("/:([]\w\.]+)/", ":\"$1\"", $jsonString);

        if (preg_match("/({.*})/", $jsonString, $matches)) {
            return $matches[0];
        }

        throw new BadJSONException(' - Syntax error, malformed JSON');
    }

    public static function getHeaders($resp)
    {
        $headers = array();

        $headerStr = substr($resp, 0, strpos($resp, "\r\n\r\n"));

        foreach (explode("\r\n", $headerStr) as $i => $line)
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }

        return $headers;
    }
}