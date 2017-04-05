<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Setup;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Widget\Model\Widget\Wysiwyg\Normalizer;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Convert conditions_encoded part of layout update data from serialized to JSON format
 */
class LayoutUpdateConverter extends SerializedToJson
{
    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * LayoutUpdateConverter constructor.
     *
     * @param Serialize $serialize
     * @param Json $json
     * @param Normalizer $normalizer
     */
    public function __construct(
        Serialize $serialize,
        Json $json,
        Normalizer $normalizer
    ) {
        $this->normalizer = $normalizer;
        parent::__construct($serialize, $json);
    }

    /**
     * Convert conditions_encoded part of layout update data from serialized to JSON format
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        preg_match_all(
            '/^(.*?conditions_encoded<\/argument><argument [^<]*>)(.*?)(<\/argument>.*?)$/si',
            $value,
            $matches,
            PREG_SET_ORDER
        );
        if (isset($matches[0])) {
            $matchSegments = $matches[0];
            $matchSegments[2] = $this->normalizer->replaceReservedCharaters(
                parent::convert($this->normalizer->restoreReservedCharaters($matchSegments[2]))
            );
            return $matchSegments[1] . $matchSegments[2] . $matchSegments[3];
        } else {
            return $value;
        }
    }
}
