<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\AttributeValueFactory;

class Document extends AbstractSimpleObject implements DocumentInterface
{
    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @param AttributeValueFactory $attributeValueFactory
     * @param array $data
     */
    public function __construct(AttributeValueFactory $attributeValueFactory, array $data = [])
    {
        parent::__construct($data);
        $this->attributeValueFactory = $attributeValueFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttribute($attributeCode)
    {
        $resultAttribute = null;
        /** @var \Magento\Framework\Api\AttributeInterface[] $attributes */
        $attributes = is_array($this->_get(self::CUSTOM_ATTRIBUTES)) ? $this->_get(self::CUSTOM_ATTRIBUTES) : [];
        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() === $attributeCode) {
                $resultAttribute = $attribute;
                break;
            }
        }

        return $resultAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $isAlreadyAdded = false;
        /** @var \Magento\Framework\Api\AttributeInterface[] $attributes */
        $attributes = is_array($this->getCustomAttributes()) ? $this->getCustomAttributes() : [];
        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() === $attributeCode) {
                $attribute->setValue($attributeValue);
                $isAlreadyAdded = true;
                break;
            }
        }
        if (!$isAlreadyAdded) {
            $attributes[] = $this->attributeValueFactory->create()
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue);
        }
        return $this->setData(self::CUSTOM_ATTRIBUTES, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributes()
    {
        return $this->_get(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $attributes);
    }
}
