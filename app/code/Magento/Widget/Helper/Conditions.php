<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Unserialize\SecureUnserializer;

/**
 * Widget Conditions helper
 */
class Conditions
{
    /**
     * @var SecureUnserializer
     */
    private $unserializer;

    /**
     * @param SecureUnserializer|null $unserializer
     */
    public function __construct(
        SecureUnserializer $unserializer = null
    ) {
        $this->unserializer = $unserializer ?: ObjectManager::getInstance()->get(SecureUnserializer::class);
    }

    /**
     * Encode widget conditions to be used with WYSIWIG
     *
     * @param array $value
     * @return string
     */
    public function encode(array $value)
    {
        $value = str_replace(['{', '}', '"', '\\'], ['[', ']', '`', '|'], serialize($value));
        return $value;
    }

    /**
     * Decode previously encoded widget conditions
     *
     * @param string $value
     * @return array
     */
    public function decode($value)
    {
        $value = str_replace(['[', ']', '`', '|'], ['{', '}', '"', '\\'], $value);

        return $this->unserializer->unserialize($value);
    }
}
