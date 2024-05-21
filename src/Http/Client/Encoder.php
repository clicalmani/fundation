<?php
namespace Clicalmani\Fundation\Http\Client;

use Clicalmani\Fundation\Http\Client\Serializer\Form;
use Clicalmani\Fundation\Http\Client\Serializer\Json;
use Clicalmani\Fundation\Http\Client\Serializer\Multipart;
use Clicalmani\Fundation\Http\Client\Serializer\Text;

/**
 * Class Encoder
 * 
 * Encoding class for serializing and deserializing request/response.
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class Encoder
{
    /**
     * Serializers
     * 
     * @var Object[]
     */
    private $serializers = [];

    public function __construct()
    {
        $this->serializers[] = new Json();
        $this->serializers[] = new Text();
        $this->serializers[] = new Multipart();
        $this->serializers[] = new Form();
    }

    /**
     * Serialize request
     * 
     * @param HttpRequest $request
     * @return string|false
     */
    public function serializeRequest(HttpRequest $request) : string|false
    {
        if (!array_key_exists('content-type', $request->headers)) {
            $message = "HttpRequest does not have Content-Type header set";
            echo $message;
            throw new \Exception($message);
        }

        $contentType = $request->headers['content-type'];
        $serializer = $this->serializer($contentType);

        if (is_null($serializer)) {
            $message = sprintf("Unable to serialize request with Content-Type: %s. Supported encodings are: %s", $contentType, implode(", ", $this->supportedEncodings()));
            echo $message;
            throw new \Exception($message);
        }

        if (!(is_string($request->body) || is_array($request->body))) {
            $message = "Body must be either string or array";
            echo $message;
            throw new \Exception($message);
        }

        $serialized = $serializer->encode($request);

        if (array_key_exists("content-encoding", $request->headers) && $request->headers["content-encoding"] === "gzip") {
            $serialized = gzencode($serialized);
        }

        return $serialized;
    }

    /**
     * Deserialize response
     * 
     * @param string $responseBody
     * @param array $headers
     * @return object|string representing the deserialized body.
     */
    public function deserializeResponse(string $responseBody, array $headers)
    {
        if (!array_key_exists('content-type', $headers)) {
            $message = "HTTP response does not have Content-Type header set";
            echo $message;
            throw new \Exception($message);
        }

        $contentType = $headers['content-type'];
        $serializer = $this->serializer($contentType);

        if (is_null($serializer)) {
            throw new \Exception(sprintf("Unable to deserialize response with Content-Type: %s. Supported encodings are: %s", $contentType, implode(", ", $this->supportedEncodings())));
        }

        if (array_key_exists("content-encoding", $headers) && $headers["content-encoding"] === "gzip") {
            $responseBody = gzdecode($responseBody);
        }

        return $serializer->decode($responseBody);
    }

    /**
     * Serializer
     * 
     * @param string $contentType
     * @return Serializer|NULL
     */
    private function serializer(string $contentType)
    {
        foreach ($this->serializers as $serializer) {
            try {
                if (preg_match($serializer->contentType(), $contentType) == 1) {
                    return $serializer;
                }
            } catch (\Exception $e) {
                $message = sprintf("Error while checking content type of %s: %s", get_class($serializer), $e->getMessage());
                echo $message;
                throw new \Exception($message, $e->getCode(), $e);
            }
        }

        return NULL;
    }

    private function supportedEncodings()
    {
        return collection()
                    ->asSet()
                    ->exchange($this->serializers)
                    ->map(fn($serializer) => $serializer->contentType())
                    ->toArray();
    }
}
