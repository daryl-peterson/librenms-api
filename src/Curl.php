<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Curl.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Curl
{
    public const API_PATH = '/api/v0';

    protected string $url;
    protected string $token;

    public function __construct(string $url, string $token)
    {
        $url = str_replace(self::API_PATH, '', $url);
        $this->url = rtrim($url, '/').self::API_PATH;
        $this->token = $token;
    }

    public function getApiUrl(string $part): string
    {
        $url = $this->url;

        $url = rtrim($url, '/');
        $part = ltrim($part, '/');
        $result = $url."/$part";

        return $result;
    }

    public function init(string $url): \CurlHandle
    {
        $curl = curl_init($url);

        if (!$curl instanceof \CurlHandle) {
            throw new ApiException('Curl failure');
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'X-Auth-Token: '.$this->token,
            'User-Agent: PHP',
            'Accept: */*',
            'Content-Type: application/json',
        ]);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        return $curl;
    }

    /**
     * Do a curl get request.
     */
    public function get(string $url): array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        return $this->response($curl, $url);
    }

    /**
     * Curl post method.
     */
    public function post(string $url, array $data = null): array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        if (isset($data)) {
            $payload = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        return $this->response($curl, $url);
    }

    /**
     * Curl delete method.
     */
    public function delete(string $url): ?array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->response($curl, $url);
    }

    /**
     * Curl patch method.
     */
    public function patch(string $url, array $data = null): ?array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');

        if (isset($data)) {
            $payload = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        return $this->response($curl, $url);
    }

    public function put(string $url, array $data = null): ?array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

        if (isset($data)) {
            $payload = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        return $this->response($curl, $url);
    }

    /**
     * Get response.
     *
     * @throws ApiException
     */
    private function response(\CurlHandle $curl, string $url): array
    {
        $response = curl_exec($curl);
        if (!$response) {
            $error = curl_error($curl);
            if (empty($error)) {
                $error = 'Unknown curl error occurred';
            }
            // throw new ApiException("Connection failure : $url");
            throw new ApiException($error);
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = $this->headersToArray(substr($response, 0, $headerSize));
        $type = $headers['Content-Type'];
        $body = substr($response, $headerSize);

        curl_close($curl);
        $result = [];
        $result['headers'] = $headers;
        $result['code'] = $code;

        switch ($type) {
            case 'application/json':
                $result = array_merge($result, (array) json_decode($body));
                break;
            case 'image/png':
                $result['image'] = ['type' => $type, 'src' => $body];
                break;
        }

        if (!($code >= 200) && ($code <= 299)) {
            if (isset($result['message'])) {
                throw new ApiException($result['message']);
            } else {
                if ($code >= 300) {
                    throw new ApiException('Please verify url');
                }
                throw new ApiException("Please check your settings URL : $url");
            }
        }

        if (isset($result['status'])) {
            if ('error' === $result['status']) {
                if (isset($result['message'])) {
                    throw new ApiException($result['message']." URL : $url");
                } else {
                    if ($code >= 300) {
                        throw new ApiException('Please verify url');
                    }
                }
                throw new ApiException("Please check your settings URL : $url");
            }
        }

        return $result;
    }

    /**
     * Convert header string to array.
     */
    private function headersToArray(string $str): array
    {
        $headers = [];
        $headersTmpArray = explode("\r\n", $str);
        for ($i = 0; $i < count($headersTmpArray); ++$i) {
            // We dont care about the two \r\n lines at the end of the headers
            if (strlen($headersTmpArray[$i]) > 0) {
                // The headers start with HTTP status codes, which do not contain a colon so we can filter them out too
                if (strpos($headersTmpArray[$i], ':')) {
                    $headerName = trim(substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ':')));
                    $headerValue = trim(substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ':') + 1));
                    $headers[$headerName] = str_replace('"', '', $headerValue);
                }
            }
        }

        return $headers;
    }
}
