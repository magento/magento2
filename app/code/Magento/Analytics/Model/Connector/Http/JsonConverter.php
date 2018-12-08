<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\Analytics\Model\Connector\Http;

=======
declare(strict_types=1);

namespace Magento\Analytics\Model\Connector\Http;

use Magento\Framework\Serialize\Serializer\Json;

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
/**
 * Represents JSON converter for http request and response body.
 */
class JsonConverter implements ConverterInterface
{
    /**
     * Content-Type HTTP header for json.
<<<<<<< HEAD
=======
     * @deprecated
     * @see CONTENT_MEDIA_TYPE
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    const CONTENT_TYPE_HEADER = 'Content-Type: application/json';

    /**
<<<<<<< HEAD
=======
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param string $body
     *
     * @return array
     */
    public function fromBody($body)
    {
<<<<<<< HEAD
        $decodedBody = json_decode($body, 1);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**
=======
        $decodedBody = $this->serializer->unserialize($body);
        return $decodedBody === null ? [$body] : $decodedBody;
    }

    /**c
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param array $data
     *
     * @return string
     */
    public function toBody(array $data)
    {
<<<<<<< HEAD
        return json_encode($data);
=======
        return $this->serializer->serialize($data);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }

    /**
     * @return string
     */
    public function getContentTypeHeader()
    {
<<<<<<< HEAD
        return self::CONTENT_TYPE_HEADER;
=======
        return sprintf('Content-Type: %s', self::CONTENT_MEDIA_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getContentMediaType(): string
    {
        return self::CONTENT_MEDIA_TYPE;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }
}
