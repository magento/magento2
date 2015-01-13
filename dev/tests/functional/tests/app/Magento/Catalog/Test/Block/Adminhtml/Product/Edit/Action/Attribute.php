<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action;

use Magento\Backend\Test\Block\Widget\Form;
use Mtf\Client\Element;

/**
 * Product attribute massaction edit page.
 */
class Attribute extends Form
{
    /**
     * CSS selector for 'save' button.
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="page-actions-toolbar-save-button"]';

    /**
     * XPath selector for checkbox that enables price editing.
     *
     * @var string
     */
    protected $priceFieldEnablerSelector = '//*[@id="attribute-price-container"]/div[1]/div/label//*[@type="checkbox"]';

    /**
     * Enable price field editing.
     *
     * @return void
     */
    public function enablePriceEdit()
    {
        $this->_rootElement->find(
            $this->priceFieldEnablerSelector,
            Element\Locator::SELECTOR_XPATH,
            'checkbox'
        )->setValue('Yes');
    }
}
