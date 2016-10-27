<?php

namespace DreamFactory\DocumentDb;

/**
 * Class Client
 *
 * @package DreamFactory\DocumentDb
 */
class Client
{
    /** API Version */
    const X_MS_VERSION = '2015-12-16';

    /** User Agent name */
    const USER_AGENT = 'df.documentdb.php.sdk/0.1.0';

    /** @var bool Debug flag */
    public static $debug = false;

    /** @var null|string Azure DocumentDB endpoint */
    protected $uri = null;

    /** @var null|string Azure DocumentDB API Key */
    protected $key = null;

    /** @var string API Version */
    protected $apiVersion = self::X_MS_VERSION;

    /**
     * Client constructor.
     *
     * @param string $uri Azure DocumentDB endpoint
     * @param string $key Azure DocumentDB API key
     */
    public function __construct($uri, $key)
    {
        $this->uri = $uri;
        $this->key = $key;
    }

    /**
     * Set API version to use.
     *
     * @param string $version API version date
     */
    public function setApiVersion($version)
    {
        $this->apiVersion = $version;
    }

    /**
     * Makes a REST API call
     *
     * @param string $verb         Request Method (HEAD, GET, POST, PUT, DELETE)
     * @param string $resourcePath Requested resource path
     * @param string $resourceType Requested resource type
     * @param string $resourceId   Requested resource id
     * @param array  $payload      Posted data
     * @param array  $extraHeaders Additional request headers
     *
     * @return mixed
     * @throws \Exception
     */
    public function request(
        $verb,
        $resourcePath,
        $resourceType,
        $resourceId,
        $payload = [],
        $extraHeaders = []
    ){
        $query = false;
        $length = 0;
        $data = '';
        if (!empty($payload)) {
            $query = (isset($payload['query'])) ? true : $query;
            $data = json_encode($payload);
            $length = strlen($data);
        }

        $headers = $this->generateRequestHeaders($verb, $resourceType, $resourceId, $query, $length, $extraHeaders);
        $url = $this->uri . '/' . trim(trim($resourcePath), '/');

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_DEFAULT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE        => static::$debug,
            CURLOPT_HTTPHEADER     => $headers
        ];

        if ($verb === Verbs::POST) {
            $options[CURLOPT_POST] = true;
        }

        if ($verb === Verbs::HEAD) {
            $options[CURLOPT_NOBODY] = true;
        }

        if (in_array($verb, [Verbs::POST, Verbs::PUT, Verbs::DELETE])) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        if (!in_array($verb, [Verbs::HEAD, Verbs::GET, Verbs::POST])) {
            $options[CURLOPT_CUSTOMREQUEST] = $verb;
        }

        print_r($options);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);

            return json_decode($response, true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Generates request headers based on request options.
     *
     * @param string $verb          Request Method (HEAD, GET, POST, PUT, DELETE)
     * @param string $resourceType  Requested resource type
     * @param string $resourceId    Requested resource id
     * @param bool   $isQuery       Indicates if the request is query or not
     * @param int    $contentLength Content length of posted data
     * @param array  $extraHeaders  Additional request headers
     *
     * @return array
     */
    protected function generateRequestHeaders(
        $verb,
        $resourceType,
        $resourceId,
        $isQuery = false,
        $contentLength = 0,
        $extraHeaders = []
    ){
        $xMsDate = gmdate('D, d M Y H:i:s T');
        $headers = [
            'Accept: application/json',
            'User-Agent: ' . static::USER_AGENT,
            'Cache-Control: no-cache',
            'x-ms-date: ' . $xMsDate,
            'x-ms-version: ' . $this->apiVersion,
            'Authorization: ' . $this->generateAuthHeader($verb, $xMsDate, $resourceType, $resourceId)
        ];

        if (in_array($verb, [Verbs::POST, Verbs::PUT])) {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . $contentLength;

            if ($isQuery === true) {
                $headers[] = 'Content-Type: application/query+json';
                $headers[] = 'x-ms-documentdb-isquery: True';
            }
        }

        return array_merge($headers, $extraHeaders);
    }

    /**
     * Generates the request Authorization header.
     *
     * @link   http://msdn.microsoft.com/en-us/library/azure/dn783368.aspx
     *
     * @param string $verb         Request Method (HEAD, GET, POST, PUT, DELETE)
     * @param string $xMsDate      Request date/time string
     * @param string $resourceType Requested resource Type
     * @param string $resourceId   Requested resource ID
     *
     * @return string Array of Request Headers
     */
    private function generateAuthHeader($verb, $xMsDate, $resourceType, $resourceId)
    {
        $master = 'master';
        $token = '1.0';
        $key = base64_decode($this->key);
        $stringToSign = $verb . "\n" .
            $resourceType . "\n" .
            $resourceId . "\n" .
            $xMsDate . "\n" .
            "\n";
        $sig = base64_encode(hash_hmac('sha256', strtolower($stringToSign), $key, true));

        return urlencode("type=$master&ver=$token&sig=$sig");
    }
}