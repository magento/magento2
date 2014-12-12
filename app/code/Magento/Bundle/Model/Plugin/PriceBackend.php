<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Model\Plugin;

/**
 * Class PriceBackend
 *
 *  Make price validation optional for bundle dynamic
 */
class PriceBackend
{
    /**
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $object
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject,
        \Closure $proceed,
        $object
    ) {
        if ($object instanceof \Magento\Catalog\Model\Product
            && $object->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            && $object->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ) {
            return true;
        } else {
            return $proceed($object);
        }
    }
}
