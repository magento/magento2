<?php
/**
 * Factory of REST request deserializers.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Request\Deserializer;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_deserializers;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $deserializers
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $deserializers = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_deserializers = $deserializers;
    }

    /**
     * Retrieve proper deserializer for the specified content type.
     *
     * @param string $contentType
     * @return \Magento\Webapi\Controller\Rest\Request\DeserializerInterface
     * @throws \LogicException|\Magento\Webapi\Exception
     */
    public function get($contentType)
    {
        if (empty($this->_deserializers)) {
            throw new \LogicException('Request deserializer adapter is not set.');
        }
        foreach ($this->_deserializers as $deserializerMetadata) {
            $deserializerType = $deserializerMetadata['type'];
            if ($deserializerType == $contentType) {
                $deserializerClass = $deserializerMetadata['model'];
                break;
            }
        }

        if (!isset($deserializerClass) || empty($deserializerClass)) {
            throw new \Magento\Webapi\Exception(
                'Server cannot understand Content-Type HTTP header media type ' . $contentType
            );
        }

        $deserializer = $this->_objectManager->get($deserializerClass);
        if (!$deserializer instanceof \Magento\Webapi\Controller\Rest\Request\DeserializerInterface) {
            throw new \LogicException(
                'The deserializer must implement "Magento\Webapi\Controller\Rest\Request\DeserializerInterface".'
            );
        }
        return $deserializer;
    }
}
