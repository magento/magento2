<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Definition\Compiled;

use Magento\Framework\Serialize\SerializerInterface;

class Json extends \Magento\Framework\ObjectManager\Definition\Compiled
{
    /**
     * Mode name
     */
    const MODE_NAME  = 'json';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return $this->getSerializer()->unserialize($signature);
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
