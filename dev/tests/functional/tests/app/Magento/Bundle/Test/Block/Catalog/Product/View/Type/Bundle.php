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

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Magento\Catalog\Test\Block\Product\View\CustomOptions;

/**
 * Class Bundle
 * Catalog bundle product info block
 */
class Bundle extends CustomOptions
{
    /**
     * Fill bundle options
     *
     * @param array $bundleOptions
     * @return void
     */
    public function fillBundleOptions($bundleOptions)
    {
        $index = 1;
        foreach ($bundleOptions as $option) {
            /** @var $optionBlock \Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\Radio|
             * \Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\Select */
            $getClass = 'getMagentoBundleCatalogProductViewTypeOption' . ucfirst($option['type']);
            $optionBlock = Factory::getBlockFactory()->$getClass(
                $this->_rootElement->find('.field.option.required:nth-of-type(' . $index++ . ')')
            );
            $optionBlock->fillOption($option);
        }
    }
}
