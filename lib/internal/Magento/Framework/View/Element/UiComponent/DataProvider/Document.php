<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Profiler;

/**
 * The document data provider
 */
class Document extends DataObject implements DocumentInterface
{
    /**
     * @var string|int
     */
    protected $id;

    /**
     * @var AttributeValueFactory
     */
    protected $attributeValueFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValue
     */
    private $attributeValuePrototype;

    /**
     * @param AttributeValueFactory $attributeValueFactory
     */
    public function __construct(AttributeValueFactory $attributeValueFactory)
    {
        $this->attributeValueFactory = $attributeValueFactory;
    }

    /**
     * Gets ID.
     *
     * @return int|string
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = $this->getIdFieldName() !== null
                ? $this->getData($this->getIdFieldName())
                : null;
        }
        return $this->id;
    }

    /**
     * Sets ID.
     *
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode)
    {
        $attributeValue = $this->getNewEmptyAttributeValue();
        $attributeValue->setAttributeCode($attributeCode);
        $attributeValue->setValue($this->getData($attributeCode));
        return $attributeValue;
    }

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $this->setData($attributeCode, $attributeValue);
        return $this;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        $output = [];
        foreach ($this->getData() as $key => $value) {
            $attribute = $this->getNewEmptyAttributeValue();
            $output[] = $attribute->setAttributeCode($key)->setValue($value);
        }
        return $output;
    }

    /**
     * Set array of custom attributes
     *
     * @param AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setCustomAttributes(array $attributes)
    {
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $this->setData(
                $attribute->getAttributeCode(),
                $attribute->getValue()
            );
        }
        return $this;
    }

    /**
     * Returns new instance of attribute value
     *
     * @return AttributeInterface
     */
    private function getNewEmptyAttributeValue(): AttributeInterface
    {
        if ($this->attributeValuePrototype === null) {
            $this->attributeValuePrototype = $this->attributeValueFactory->create();
        }
        return clone $this->attributeValuePrototype;
    }
}
