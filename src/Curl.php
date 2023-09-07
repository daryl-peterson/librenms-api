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
    public array|null $result;

    public function __construct(string $url, string $token)
    {
        $url = str_replace(self::API_PATH, '', $url);
        $this->url = rtrim($url, '/').self::API_PATH;
        $this->token = $token;
        $this->result = [];
    }

    public function getApiUrl(string $part): string
    {
        $url = $this->url;

        $url = rtrim($url, '/');
        $part = ltrim($part, '/');
        $return = $url."/$part";

        return $return;
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

    public function put(string $url, array|object $data = null): ?array
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

        if (isset($data)) {
            $payload = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        return $this->response($curl, $url);
    }

    private function init(string $url): \CurlHandle
    {
        $curl = curl_init($url);

        if (!$curl instanceof \CurlHandle) {
            // @codeCoverageIgnoreStart
            throw new ApiException('Curl failure');
            // @codeCoverageIgnoreEnd
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

        return $curl;
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
            $errNumb = curl_errno($curl);
            switch ($errNumb) {
                case 3:
                    $error = 'URL malformed';
                    break;
                case 6:
                    $error = 'Could not resolve host';
                    break;
                case 28:
                    $error = 'Could not connect';
                    break;
                default:
                    // @codeCoverageIgnoreStart
                    if (empty($error)) {
                        $error = 'Unknow curl error';
                    }
                    break;
                    // @codeCoverageIgnoreEnd
            }

            throw new ApiException($error);
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = $this->headersToArray(substr($response, 0, $headerSize));
        $type = $headers['Content-Type'];
        $body = substr($response, $headerSize);

        curl_close($curl);
        $this->result = [];
        $this->result['headers'] = $headers;
        $this->result['code'] = $code;

        switch ($type) {
            case 'application/json':
                $this->result = array_merge($this->result, (array) json_decode($body));
                break;
            case 'image/png':
                $this->result['image'] = ['type' => $type, 'src' => $body];
                break;
        }

        if (($code >= 200) && ($code <= 299)) {
            return $this->result;
        }

        if ($code >= 300) {
            // @codeCoverageIgnoreStart
            throw new ApiException($this->result['message']);
            // @codeCoverageIgnoreEnd
        }
        // @codeCoverageIgnoreStart
        return $this->result;
        // @codeCoverageIgnoreEnd
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
