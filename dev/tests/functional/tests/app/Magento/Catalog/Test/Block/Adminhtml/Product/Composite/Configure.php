<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Composite;

use Magento\Backend\Test\Block\Template;
use Magento\Catalog\Test\Block\AbstractConfigureBlock;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Configure
 * Adminhtml catalog product composite configure block
 */
class Configure extends AbstractConfigureBlock
{
    /**
     * Custom options CSS selector
     *
     * @var string
     */
    protected $customOptionsSelector = '#product_composite_configure_fields_options';

    /**
     * Selector for "Ok" button
     *
     * @var string
     */
    protected $okButton = '.ui-dialog-buttonset button:nth-of-type(2)';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Set quantity
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_fill($this->dataMapping(['qty' => $qty]));
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function configProduct(FixtureInterface $product)
    {
        $checkoutData = null;
        if ($product instanceof InjectableFixture) {
            /** @var CatalogProductSimple $product */
            $checkoutData = $product->getCheckoutData();
        }

        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickOk();
    }

    /**
     * Click "Ok" button
     *
     * @return void
     */
    public function clickOk()
    {
        $this->_rootElement->find($this->okButton)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
