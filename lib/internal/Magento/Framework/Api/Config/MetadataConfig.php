<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Config;

use Magento\Framework\Api\AttributeMetadataBuilderInterface;
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
     * @var AttributeMetadataBuilderInterface
     */
    private $attributeMetadataBuilder;

    /**
     * Initialize dependencies.
     *
     * @param ServiceConfigReader $serviceConfigReader
     * @param AttributeMetadataBuilderInterface $attributeMetadataBuilder
     */
    public function __construct(
        ServiceConfigReader $serviceConfigReader,
        AttributeMetadataBuilderInterface $attributeMetadataBuilder
    ) {
        $this->serviceConfigReader = $serviceConfigReader;
        $this->attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $attributes = [];
        if (!is_null($this->attributeMetadataBuilder) && !is_null($dataObjectClassName)) {
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
        $allAttributes = $this->serviceConfigReader->read();
        if (isset($allAttributes[$dataObjectClassName])
            && is_array($allAttributes[$dataObjectClassName])
        ) {
            $attributeCodes = array_keys($allAttributes[$dataObjectClassName]);
            foreach ($attributeCodes as $attributeCode) {
                $this->attributeMetadataBuilder->setAttributeCode($attributeCode);
                $attributes[$attributeCode] = $this->attributeMetadataBuilder->create();
            }
        }
        return $attributes;
    }
}
