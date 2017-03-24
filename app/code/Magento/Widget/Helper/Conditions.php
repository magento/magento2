<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Widget Conditions helper.
 */
class Conditions
{
    const WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP = [
        '{' => '[',
        '}' => ']',
        '"' => '`',
        '\\' => '|',
    ];

    /**
     * Instance of serializer interface.
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Encode widget conditions to be used with WYSIWIG.
     *
     * @param array $value
     * @return string
     */
    public function encode(array $value)
    {
        $value = str_replace(
            array_keys(self::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_values(self::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $this->serializer->serialize($value)
        );
        return $value;
    }

    /**
     * Decode previously encoded widget conditions.
     *
     * @param string $value
     * @return array
     */
    public function decode($value)
    {
        $value = str_replace(
            array_values(self::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_keys(self::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $value
        );
        $value = $this->serializer->unserialize($value);
        return $value;
    }
}
