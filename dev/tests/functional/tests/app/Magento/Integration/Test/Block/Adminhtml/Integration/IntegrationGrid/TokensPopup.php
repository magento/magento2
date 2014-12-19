<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
