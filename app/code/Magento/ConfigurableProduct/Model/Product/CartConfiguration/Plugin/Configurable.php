<?php
/**
 * Plugin for cart product configuration
 *
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
namespace Magento\ConfigurableProduct\Model\Product\CartConfiguration\Plugin;

class Configurable
{
    /**
     * Decide whether product has been configured for cart or not
     *
     * @param \Magento\Catalog\Model\Product\CartConfiguration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param array $config
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsProductConfigured(
        \Magento\Catalog\Model\Product\CartConfiguration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        $config
    ) {
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return isset($config['super_attribute']);
        }

        return $proceed($product, $config);
    }
}
