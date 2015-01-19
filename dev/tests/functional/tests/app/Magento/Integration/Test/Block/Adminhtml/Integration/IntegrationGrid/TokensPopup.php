<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid;

use Mtf\Block\Form;

/**
 * Class TokensPopup
 * Integration tokens popup container
 */
class TokensPopup extends Form
{
    /**
     * Selector for "Done" button
     *
     * @var string
     */
    protected $doneButtonSelector = '.primary[role="button"]';

    /**
     * Click Done button on Integration tokens popup window
     *
     * @return void
     */
    public function clickDoneButton()
    {
        $this->_rootElement->find($this->doneButtonSelector)->click();
    }
}
