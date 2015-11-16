<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Widget options form.
 */
class Settings extends Tab
{
    /**
     * 'Continue' button locator.
     *
     * @var string
     */
    protected $continueButton = './/button[contains(@data-ui-id, "widget-button")]';

    /**
     * Click 'Continue' button.
     *
     * @return void
     */
    protected function clickContinue()
    {
        $this->_rootElement->find($this->continueButton, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        parent::fillFormTab($fields, $element);
        $this->clickContinue();

        return $this;
    }
}
