<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $checkDataButton = '#upload_button';

    /**
     * Magento new loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Magento varienLoader.js loader.
     *
     * @var string
     */
    protected $loaderOld = '//ancestor::body/div[@id="loading-mask"]';

    /**
     * Click "Check Data" button.
     *
     * @return void
     */
    public function clickCheckData()
    {
        $this->waitForElementVisible($this->checkDataButton);
        $this->_rootElement->find($this->checkDataButton)->click();
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        $this->waitForElementNotVisible($this->loaderOld, Locator::SELECTOR_XPATH);
    }
}
