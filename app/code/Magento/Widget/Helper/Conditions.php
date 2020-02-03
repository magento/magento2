<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Helper;

use Magento\Framework\Data\Wysiwyg\Normalizer;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Widget Conditions helper.
 */
class Conditions
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @param Json $serializer
     * @param Normalizer $normalizer
     */
    public function __construct(
        Json $serializer,
        Normalizer $normalizer
    ) {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
    }

    /**
     * Encode widget conditions to be used with WYSIWIG.
     *
     * @param array $value
     * @return string
     */
    public function encode(array $value)
    {
        return $this->normalizer->replaceReservedCharacters($this->serializer->serialize($value));
    }

    /**
     * Decode previously encoded widget conditions.
     *
     * @param string $value
     * @return array
     */
    public function decode($value)
    {
        return $this->serializer->unserialize(
            $this->normalizer->restoreReservedCharacters($value)
        );
    }
}
