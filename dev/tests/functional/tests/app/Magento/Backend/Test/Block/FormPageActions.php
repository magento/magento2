<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Client\Locator;

/**
 * Class FormPageActions
 * Form page actions block
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class FormPageActions extends PageActions
{
    /**
     * "Back" button.
     *
     * @var string
     */
    protected $backButton = '#back';

    /**
     * "Reset" button.
     *
     * @var string
     */
    protected $resetButton = '#reset';

    /**
     * "Save and Continue Edit" button.
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_continue';

    /**
     * "Save" button.
     *
     * @var string
     */
    protected $saveButton = '#save';

    /**
     * "Delete" button.
     *
     * @var string
     */
    protected $deleteButton = '.delete';

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
     * Click "Back" button.
     */
    public function back()
    {
        $this->_rootElement->find($this->backButton)->click();
    }

    /**
     * Click "Reset" button.
     */
    public function reset()
    {
        $this->waitBeforeClick();
        $this->_rootElement->find($this->resetButton)->click();
    }

    /**
     * Click "Save and Continue Edit" button.
     */
    public function saveAndContinue()
    {
        $this->waitBeforeClick();
        $this->_rootElement->find($this->saveAndContinueButton)->click();
        $this->waitForElementNotVisible('.popup popup-loading');
        $this->waitForElementNotVisible('.loader');
    }

    /**
     * Click "Save" button.
     */
    public function save()
    {
        $this->waitBeforeClick();
        $this->_rootElement->find($this->saveButton)->click();
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        $this->waitForElementNotVisible($this->loaderOld, Locator::SELECTOR_XPATH);
    }

    /**
     * Click "Delete" button.
     */
    public function delete()
    {
        $this->waitBeforeClick();
        $this->_rootElement->find($this->deleteButton)->click();
    }

    /**
     * Check 'Delete' button availability.
     *
     * @return bool
     */
    public function checkDeleteButton()
    {
        return $this->_rootElement->find($this->deleteButton)->isVisible();
    }

    /**
     * Wait for User before clicking any Button which calls JS validation on correspondent form.
     * See details in MAGETWO-31121.
     *
     * @return void
     */
    protected function waitBeforeClick()
    {
        time_nanosleep(0, 600000000);
        usleep(1000000);
    }
}
