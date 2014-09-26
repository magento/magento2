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

namespace Magento\Bundle\Test\Block\Catalog\Product;

use Mtf\Client\Element\Locator;
use Magento\Bundle\Test\Block\Catalog\Product\View\Type\Bundle;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Class View
 * Bundle product view block on the product page
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Customize and add to cart button selector
     *
     * @var string
     */
    protected $customizeButton = '.action.primary.customize';

    /**
     * Bundle options block
     *
     * @var string
     */
    protected $bundleBlock = '//*[@id="product-options-wrapper"]//fieldset[contains(@class,"bundle")]';

    /**
     * Get bundle options block
     *
     * @return Bundle
     */
    public function getBundleBlock()
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Catalog\Product\View\Type\Bundle',
            ['element' => $this->_rootElement->find($this->bundleBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Click "Customize and add to cart button"
     *
     * @return void
     */
    public function clickCustomize()
    {
        $this->_rootElement->find($this->customizeButton)->click();
        $this->waitForElementVisible($this->addToCart);
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product [optional]
     * @return array
     */
    public function getOptions(FixtureInterface $product = null)
    {
        $options = [];

        $this->clickCustomize();
        $options['bundle_options'] = $this->getBundleBlock()->getOptions($product);
        $options += parent::getOptions($product);

        return $options;
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        if ($product instanceof InjectableFixture) {
            /** @var \Magento\Bundle\Test\Fixture\BundleProduct $product */
            $checkoutData = $product->getCheckoutData();
            $bundleCheckoutData = isset($checkoutData['options']['bundle_options'])
                ? $checkoutData['options']['bundle_options']
                : [];
        } else {
            // TODO: Removed after refactoring(removed) old product fixture.
            /** @var \Magento\Bundle\Test\Fixture\BundleFixed $product */
            $bundleCheckoutData = $product->getSelectionData();
        }
        if (!$this->getBundleBlock()->isVisible()) {
            $this->_rootElement->find($this->customizeButton)->click();
        }
        $this->getBundleBlock()->fillBundleOptions($bundleCheckoutData);

        parent::fillOptions($product);
    }
}
