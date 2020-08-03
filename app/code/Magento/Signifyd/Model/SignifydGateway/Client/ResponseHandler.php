<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Client;

use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Framework\Json\DecoderInterface;

/**
 * Class ResponseHandler
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class ResponseHandler
{
    /**
     * Successful HTTP response codes.
     *
     * @var array
     */
    private static $successResponseCodes = [200, 201, 204];

    /**
     * Current servers PHP version id.
     */
    private static $phpVersionId = PHP_VERSION_ID;

    /**
     * Failure HTTP response codes with messages.
     *
     * @var array
     */
    private static $failureResponses = [
        400 => 'Bad Request - The request could not be parsed. Response: %s',
        401 => 'Unauthorized - user is not logged in, could not be authenticated. Response: %s',
        403 => 'Forbidden - Cannot access resource. Response: %s',
        404 => 'Not Found - resource does not exist. Response: %s',
        409 => 'Conflict - with state of the resource on server. Can occur with (too rapid) PUT requests. Response: %s',
        500 => 'Server error. Response: %s'
    ];

    /**
     * Unexpected Signifyd API response message.
     *
     * @var string
     */
    private static $unexpectedResponse = 'Unexpected Signifyd API response code "%s" with content "%s".';

    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * ResponseHandler constructor.
     *
     * @param DecoderInterface $dataDecoder
     */
    public function __construct(
        DecoderInterface $dataDecoder
    ) {
        $this->dataDecoder = $dataDecoder;
    }

    /**
     * Reads result of successful operation and throws exception in case of any failure.
     *
     * @param \Zend_Http_Response $response
     * @return array
     * @throws ApiCallException
     */
    public function handle(\Zend_Http_Response $response)
    {
        $responseCode = $response->getStatus();

        if (!in_array($responseCode, self::$successResponseCodes)) {
            $errorMessage = $this->buildApiCallFailureMessage($response);
            throw new ApiCallException($errorMessage);
        }

        $responseBody = (string)$response->getBody();

        if (self::$phpVersionId < 70000 && empty($responseBody)) {
            /*
             * Only since PHP 7.0 empty string treated as JSON syntax error
             * http://php.net/manual/en/function.json-decode.php
             */
            throw new ApiCallException('Response is not valid JSON: Decoding failed: Syntax error');
        }

        try {
            $decodedResponseBody = $this->dataDecoder->decode($responseBody);
        } catch (\Exception $e) {
            throw new ApiCallException(
                'Response is not valid JSON: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $decodedResponseBody;
    }

    /**
     *  Error message for request rejected by Signify.
     *
     * @param \Zend_Http_Response $response
     * @return string
     */
    private function buildApiCallFailureMessage(\Zend_Http_Response $response)
    {
        $responseBody = $response->getBody();

        if (key_exists($response->getStatus(), self::$failureResponses)) {
            return sprintf(self::$failureResponses[$response->getStatus()], $responseBody);
        }

        return sprintf(
            self::$unexpectedResponse,
            $response->getStatus(),
            $responseBody
        );
    }
}
