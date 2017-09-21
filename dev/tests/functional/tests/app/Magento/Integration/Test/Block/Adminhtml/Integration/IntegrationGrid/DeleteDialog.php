<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class DeleteDialog
 * Delete Dialog confirmation window
 */
class DeleteDialog extends Block
{
    /**
     * Delete button selector
     *
     * @var string
     */
    protected $deleteButton = './/button/span[@class="ui-button-text"][text()="Delete"]';

    /**
     * Click on delete button
     *
     * @return void
     */
    public function acceptDeletion()
    {
        $this->_rootElement->find($this->deleteButton, Locator::SELECTOR_XPATH)->click();
    }
}
