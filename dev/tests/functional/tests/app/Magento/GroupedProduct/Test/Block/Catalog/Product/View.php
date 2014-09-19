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

namespace Magento\GroupedProduct\Test\Block\Catalog\Product;

use Magento\Catalog\Test\Block\Product\View as ParentView;
use Mtf\Fixture\FixtureInterface;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;

/**
 * Class View
 * Grouped product view block on the product page
 */
class View extends ParentView
{
    /**
     * Block grouped product
     *
     * @var string
     */
    protected $groupedProductBlock = '.table-wrapper.grouped';

    /**
     * This member holds the class name of the tier price block.
     *
     * @var string
     */
    protected $formatTierPrice = "//tbody[%row-number%]//ul[contains(@class,'tier')]//*[@class='item'][%line-number%]";

    /**
     * This member holds the class name of the special price block.
     *
     * @var string
     */
    protected $formatSpecialPrice = '//tbody[%row-number%]//*[contains(@class,"price-box")]';

    /**
     * Get grouped product block
     *
     * @return \Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type\Grouped
     */
    public function getGroupedProductBlock()
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type\Grouped',
            [
                'element' => $this->_rootElement->find($this->groupedProductBlock)
            ]
        );
    }

    /**
     * Change tier price selector
     *
     * @param int $index
     * @return void
     */
    public function itemTierPriceProductBlock($index)
    {
        $this->tierPricesSelector = str_replace('%row-number%', $index, $this->formatTierPrice);
    }

    /**
     * Change tier price selector
     *
     * @param int $index
     * @return void
     */
    public function itemPriceProductBlock($index)
    {
        $this->priceBlock = str_replace('%row-number%', $index, $this->formatSpecialPrice);
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {
        $groupedOptions = $this->getGroupedProductBlock()->getOptions($product);
        return ['grouped_options' => $groupedOptions] + parent::getOptions($product);
    }

    /**
     * Fill specified option for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $this->getGroupedProductBlock()->fill($product);
        parent::fillOptions($product);
    }
}
