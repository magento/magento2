<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Composite;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Block\AbstractConfigureBlock;
use Magento\Backend\Test\Block\Template;

/**
 * Adminhtml catalog product composite configure block.
 */
class Configure extends AbstractConfigureBlock
{
    /**
     * Configure form selector.
     *
     * @var string
     */
    protected $configureForm = '#product_composite_configure_form';

    /**
     * Custom options CSS selector.
     *
     * @var string
     */
    protected $customOptionsSelector = '#product_composite_configure_fields_options';

    /**
     * Product quantity selector.
     *
     * @var string
     */
    protected $qty = '[name="qty"]';

    /**
     * Selector for "Ok" button.
     *
     * @var string
     */
    protected $okButton = '.action-primary[data-role="action"]';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Set quantity.
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_rootElement->find($this->qty)->setValue($qty);
    }

    /**
     * Fill in the option specified for the product.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function configProduct(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();

        $this->waitForFormVisible();
        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickOk();
        $this->waitForFormNotVisible();
    }

    /**
     * Click "Ok" button.
     *
     * @return void
     */
    public function clickOk()
    {
        $this->_rootElement->find($this->okButton)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Wait for form is visible.
     *
     * @return void
     */
    protected function waitForFormVisible()
    {
        $this->browser->waitUntil(
            function () {
                return $this->_rootElement->find($this->configureForm)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Wait for form is not visible.
     *
     * @return void
     */
    protected function waitForFormNotVisible()
    {
        $this->browser->waitUntil(
            function () {
                return $this->_rootElement->find($this->configureForm)->isVisible() ? null : true;
            }
        );
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
