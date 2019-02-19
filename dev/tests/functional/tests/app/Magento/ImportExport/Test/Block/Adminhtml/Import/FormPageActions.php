<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import;

use Magento\Mtf\Client\Locator;

/**
 * Form page actions block.
 */
class FormPageActions extends \Magento\Backend\Test\Block\PageActions
{
    /**
     * "Check Data" button.
     *
     * @var string
     */
    private $checkDataButton = '#upload_button';

    /**
     * Magento loader.
     *
     * @var string
     */
    private $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Click "Check Data" button.
     *
     * @return void
     */
    public function clickCheckData()
    {
        $this->waitForElementVisible($this->checkDataButton);
        $this->_rootElement->find($this->checkDataButton)->click();
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
    }
}
