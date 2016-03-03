<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class AttributeLoader
 */
class AttributeLoader implements AttributeLoaderInterface
{
    /**
     * Default Attributes that are static
     *
     * @var array
     */
    private $defaultAttributes = [];

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * AttributeLoader constructor.
     *
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Config $config,
        ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @param AbstractEntity $resource
     * @param DataObject|null $object
     * @return AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAllAttributes(AbstractEntity $resource, DataObject $object = null)
    {
        $attributeCodes = $this->config->getEntityAttributeCodes($resource->getEntityType(), $object);
        /**
         * Check and init default attributes
         */
        $defaultAttributes = $resource->getDefaultAttributes();
        foreach ($defaultAttributes as $attributeCode) {
            $attributeIndex = array_search($attributeCode, $attributeCodes);
            if ($attributeIndex !== false) {
                $resource->getAttribute($attributeCodes[$attributeIndex]);
                unset($attributeCodes[$attributeIndex]);
            } else {
                $resource->addAttribute($this->_getDefaultAttribute($resource, $attributeCode));
            }
        }
        foreach ($attributeCodes as $code) {
            $resource->getAttribute($code);
        }
       return $resource;
    }


    /**
     * Return default static virtual attribute that doesn't exists in EAV attributes
     *
     * @param string $attributeCode
     * @return Attribute
     */
    protected function _getDefaultAttribute(AbstractEntity $resource, $attributeCode)
    {
        $entityTypeId = $resource->getEntityType()->getId();
        if (!isset($this->defaultAttributes[$entityTypeId][$attributeCode])) {
            $attribute = $this->objectManager->create(
                $resource->getEntityType()->getAttributeModel()
            )->setAttributeCode(
                $attributeCode
            )->setBackendType(
                AbstractAttribute::TYPE_STATIC
            )->setIsGlobal(
                1
            )->setEntityType(
                $resource->getEntityType()
            )->setEntityTypeId(
                $resource->getEntityType()->getId()
            );
            $this->defaultAttributes[$entityTypeId][$attributeCode] = $attribute;
        }
        return $this->defaultAttributes[$entityTypeId][$attributeCode];
    }
}
