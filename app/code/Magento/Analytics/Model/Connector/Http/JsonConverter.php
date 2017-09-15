<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents JSON converter for http request and response body.
 * @since 2.2.0
 */
class JsonConverter implements ConverterInterface
{
    /**
     * Content-Type HTTP header for json.
     */
    const CONTENT_TYPE_HEADER = 'Content-Type: application/json';

    /**
     * @param string $body
     *
     * @return array
     * @since 2.2.0
     */
    public function fromBody($body)
    {
        $decodedBody = json_decode($body, 1);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**
     * @param array $data
     *
     * @return string
     * @since 2.2.0
     */
    public function toBody(array $data)
    {
        return json_encode($data);
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getContentTypeHeader()
    {
        return self::CONTENT_TYPE_HEADER;
    }
}
