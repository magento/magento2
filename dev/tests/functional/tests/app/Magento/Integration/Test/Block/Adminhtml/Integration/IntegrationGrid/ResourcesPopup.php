<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid;

use Mtf\Block\Form;

/**
 * Class ResourcesPopup
 * Integration resources popup container.
 */
class ResourcesPopup extends Form
{
    /**
     * Selector for "Allow" button.
     *
     * @var string
     */
    protected $allowButtonSelector = '[data-row-dialog="tokens"][role="button"]';

    /**
     * Selector for "Reauthorize" button.
     *
     * @var string
     */
    protected $reauthorizeButtonSelector = '[data-row-dialog="tokens"][data-row-is-reauthorize="1"]';

    /**
     * Click allow button in integration resources popup window.
     *
     * @return void
     */
    public function clickAllowButton()
    {
        $this->_rootElement->find($this->allowButtonSelector)->click();
    }

    /**
     * Click reauthorize button in integration resources popup window.
     *
     * @return void
     */
    public function clickReauthorizeButton()
    {
        $this->_rootElement->find($this->reauthorizeButtonSelector)->click();
    }
}
