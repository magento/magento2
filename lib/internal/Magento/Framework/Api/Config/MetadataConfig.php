<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Config;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Config\Reader as ServiceConfigReader;
use Magento\Framework\Api\MetadataServiceInterface;

/**
 * Class which allows to get a metadata of the attributes declared in a config.
 */
class MetadataConfig implements MetadataServiceInterface
{
    /**
     * @var ServiceConfigReader
     */
    private $serviceConfigReader;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var array
     */
    private $allAttributes = [];

    /**
     * Initialize dependencies.
     *
     * @param ServiceConfigReader $serviceConfigReader
     * @param ObjectFactory $objectFactory
     */
    public function __construct(
        ServiceConfigReader $serviceConfigReader,
        ObjectFactory $objectFactory
    ) {
        $this->serviceConfigReader = $serviceConfigReader;
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $attributes = [];
        if (!is_null($this->objectFactory) && !is_null($dataObjectClassName)) {
            /**
             * Attribute metadata builder and data object class name are expected to be configured
             * via DI using virtual types. If configuration is missing, empty array should be returned.
             */
            $attributes = $this->getAttributesMetadata($dataObjectClassName);
            $implementedInterfaces = (new \ReflectionClass($dataObjectClassName))->getInterfaceNames();
            foreach ($implementedInterfaces as $interfaceName) {
                $attributes = array_merge($attributes, $this->getAttributesMetadata($interfaceName));
            }
        }
        return $attributes;
    }

    /**
     * Get custom attribute metadata for the given class/interface.
     *
     * @param string $dataObjectClassName
     * @return \Magento\Framework\Api\MetadataObjectInterface[]
     */
    protected function getAttributesMetadata($dataObjectClassName)
    {
        $attributes = [];
        if (empty($this->allAttributes)) {
            $this->allAttributes = $this->serviceConfigReader->read();
        }
        $dataObjectClassName = ltrim($dataObjectClassName, '\\');
        if (isset($this->allAttributes[$dataObjectClassName])
            && is_array($this->allAttributes[$dataObjectClassName])
        ) {
            $attributeCodes = array_keys($this->allAttributes[$dataObjectClassName]);
            foreach ($attributeCodes as $attributeCode) {
                $attributes[$attributeCode] = $this->objectFactory
                    ->create('\Magento\Framework\Api\MetadataObjectInterface', [])
                    ->setAttributeCode($attributeCode);
            }
        }
        return $attributes;
    }
}
