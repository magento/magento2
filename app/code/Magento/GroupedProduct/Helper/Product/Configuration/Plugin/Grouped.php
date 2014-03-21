<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GroupedProduct\Helper\Product\Configuration\Plugin;

class Grouped
{
    /**
     * Retrieves grouped product options list
     *
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $options = array();
            /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $associatedProducts = $typeInstance->getAssociatedProducts($product);

            if ($associatedProducts) {
                foreach ($associatedProducts as $associatedProduct) {
                    $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                    $option = array(
                        'label' => $associatedProduct->getName(),
                        'value' => $qty && $qty->getValue() ? $qty->getValue() : 0
                    );
                    $options[] = $option;
                }
            }

            $options = array_merge($options, $proceed($item));
            $isUnConfigured = true;
            foreach ($options as &$option) {
                if ($option['value']) {
                    $isUnConfigured = false;
                    break;
                }
            }
            return $isUnConfigured ? array() : $options;
        }
        return $proceed($item);
    }
}
