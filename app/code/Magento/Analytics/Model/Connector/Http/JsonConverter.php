<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

<<<<<<< HEAD
use Magento\Framework\Serialize\Serializer\Json;

=======
>>>>>>> upstream/2.2-develop
/**
 * Represents JSON converter for http request and response body.
 */
class JsonConverter implements ConverterInterface
{
    /**
     * Content-Type HTTP header for json.
     */
    const CONTENT_TYPE_HEADER = 'Content-Type: application/json';

    /**
<<<<<<< HEAD
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
=======
>>>>>>> upstream/2.2-develop
     * @param string $body
     *
     * @return array
     */
    public function fromBody($body)
    {
<<<<<<< HEAD
        $decodedBody = $this->serializer->unserialize($body);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**c
=======
        $decodedBody = json_decode($body, 1);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**
>>>>>>> upstream/2.2-develop
     * @param array $data
     *
     * @return string
     */
    public function toBody(array $data)
    {
<<<<<<< HEAD
        return $this->serializer->serialize($data);
=======
        return json_encode($data);
>>>>>>> upstream/2.2-develop
    }

    /**
     * @return string
     */
    public function getContentTypeHeader()
    {
        return self::CONTENT_TYPE_HEADER;
    }
}
