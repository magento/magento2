<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid;

use Magento\Mtf\Block\Form;

/**
 * Integration tokens popup container.
 */
class TokensPopup extends Form
{
    /**
     * Selector for "Done" button.
     *
     * @var string
     */
    protected $doneButtonSelector = '.action-primary[type="button"]';

    /**
     * Click Done button on Integration tokens popup window.
     *
     * @return void
     */
    public function clickDoneButton()
    {
        $this->_rootElement->find($this->doneButtonSelector)->click();
    }
}
