<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Less secure than Json implementation, but gives higher performance on big arrays. Does not unserialize objects on
 * PHP 7. Using this implementation directly is discouraged as it may lead to security vulnerabilities, especially on
 * older versions of PHP
 */
class Serialize implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function serialize($data)
    {
        return serialize($data);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        if ($this->getPhpVersion() >= 7) {
            return unserialize($string, ['allowed_classes' => false]);
        }
        return unserialize($string);
    }

    /**
     * Return major PHP version
     *
     * @return int
     */
    private function getPhpVersion()
    {
        return PHP_MAJOR_VERSION;
    }
}
