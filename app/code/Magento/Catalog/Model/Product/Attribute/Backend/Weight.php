<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product weight backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\Backend\Weight
 *
 * @since 2.0.0
 */
class Weight extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     * @since 2.0.0
     */
    protected $localeFormat;

    /**
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat
    ) {
        $this->localeFormat = $localeFormat;
    }

    /**
     * Validate
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     * @since 2.0.0
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!$this->isPositiveOrZero($value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please enter a number 0 or greater in this field.')
            );
        }
        return true;
    }

    /**
     * Returns whether the value is greater than, or equal to, zero
     *
     * @param mixed $value
     * @return bool
     * @since 2.0.0
     */
    protected function isPositiveOrZero($value)
    {
        $value = $this->localeFormat->getNumber($value);
        $isNegative = $value < 0;
        return  !$isNegative;
    }
}
