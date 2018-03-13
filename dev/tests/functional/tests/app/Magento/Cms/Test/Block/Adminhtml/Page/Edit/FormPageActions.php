<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page\Edit;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Product Form page actions.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Save and Continue Edit" button.
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save-button';

    /**
     * CSS selector toggle "Save button".
     *
     * @var string
     */
    private $toggleButton = '[data-ui-id="save-button-dropdown"]';

    /**
     * "Save" button.
     *
     * @var string
     */
    protected $saveButton = '#save_and_close';

    /**
     * Click on "Save" button.
     *
     * @return void
     */
    public function save()
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        parent::save();
    }
}
