<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

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
