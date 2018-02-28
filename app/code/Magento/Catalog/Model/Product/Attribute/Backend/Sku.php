<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;

/**
 * Catalog product SKU backend attribute model.
 */
class Sku extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Maximum SKU string length
     *
     * @var string
     */
    const SKU_MAX_LENGTH = 64;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(\Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->string = $string;
    }

    /**
     * Validate SKU
     *
     * @param Product $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if ($this->getAttribute()->getIsRequired() && strlen($value) === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The "%1" attribute value is empty. Set the attribute and try again.', $attrCode)
            );
        }

        if ($this->string->strlen($object->getSku()) > self::SKU_MAX_LENGTH) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('SKU length should be %1 characters maximum.', self::SKU_MAX_LENGTH)
            );
        }

        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        if (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('SKU is already in use.')
            );
        }

        return true;
    }

    /**
     * Make SKU unique before save
     *
     * @param Product $object
     * @return $this
     */
    public function beforeSave($object)
    {
        return parent::beforeSave($object);
    }
}
