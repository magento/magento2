<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Model\Connector\Http;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Represents JSON converter for http request and response body.
 */
class JsonConverter implements ConverterInterface
{
    /**
     * Content-Type HTTP header for json.
     * @deprecated
     * @see CONTENT_MEDIA_TYPE
     */
    const CONTENT_TYPE_HEADER = 'Content-Type: application/json';

    /**
     * Media-Type corresponding to this converter.
     */
    const CONTENT_MEDIA_TYPE = 'application/json';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $body
     *
     * @return array
     */
    public function fromBody($body)
    {
        $decodedBody = $this->serializer->unserialize($body);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**c
     * @param array $data
     *
     * @return string
     */
    public function toBody(array $data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getContentTypeHeader()
    {
        return sprintf('Content-Type: %s', self::CONTENT_MEDIA_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getContentMediaType(): string
    {
        return self::CONTENT_MEDIA_TYPE;
    }
}
