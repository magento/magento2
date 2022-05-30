<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Catalog product SKU backend attribute model.
 */
class Sku extends AbstractBackend
{
    /**
     * Maximum SKU string length
     *
     * @var string
     */
    public const SKU_MAX_LENGTH = 64;

    /**
     * Magento string lib
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @param StringUtils $string
     */
    public function __construct(StringUtils $string)
    {
        $this->string = $string;
    }

    /**
     * Validate SKU
     *
     * @param Product $object
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode) ?? '';
        if ($this->getAttribute()->getIsRequired() && strlen($value) === 0) {
            throw new LocalizedException(
                __('The "%1" attribute value is empty. Set the attribute and try again.', $attrCode)
            );
        }

        if ($this->string->strlen($object->getSku()) > self::SKU_MAX_LENGTH) {
            throw new LocalizedException(
                __('SKU length should be %1 characters maximum.', self::SKU_MAX_LENGTH)
            );
        }
        return true;
    }

    /**
     * Generate and set unique SKU to product
     *
     * @param Product $object
     * @return void
     */
    protected function _generateUniqueSku($object)
    {
        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        $attributeValue = $object->getData($attribute->getAttributeCode());
        $increment = null;
        while (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            if ($increment === null) {
                $increment = $this->_getLastSimilarAttributeValueIncrement($attribute, $object);
            }
            $sku = $attributeValue === null ? '' : trim($attributeValue);
            if (strlen($sku . '-' . (++$increment)) > self::SKU_MAX_LENGTH) {
                $sku = substr($sku, 0, -strlen($increment) - 1);
            }
            $sku = $sku . '-' . $increment;
            $object->setData($attribute->getAttributeCode(), $sku);
        }
    }

    /**
     * Make SKU unique before save
     *
     * @param Product $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->_generateUniqueSku($object);
        $this->trimValue($object);
        return parent::beforeSave($object);
    }

    /**
     * Return increment needed for SKU uniqueness
     *
     * @param AbstractAttribute $attribute
     * @param Product $object
     * @return int
     */
    protected function _getLastSimilarAttributeValueIncrement($attribute, $object)
    {
        $connection = $this->getAttribute()->getEntity()->getConnection();
        $select = $connection->select();
        $value = $object->getData($attribute->getAttributeCode()) ?? '';
        $bind = ['attribute_code' => trim($value) . '-%'];

        $select->from(
            $this->getTable(),
            $attribute->getAttributeCode()
        )->where(
            $attribute->getAttributeCode() . ' LIKE :attribute_code'
        )->order(
            ['entity_id DESC', $attribute->getAttributeCode() . ' ASC']
        )->limit(
            1
        );
        $data = $connection->fetchOne($select, $bind);
        return abs((int)str_replace($value, '', $data));
    }

    /**
     * Remove extra spaces from attribute value before save.
     *
     * @param Product $object
     * @return void
     */
    private function trimValue($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if ($value) {
            $object->setData($attrCode, trim($value));
        }
    }
}
