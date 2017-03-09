<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import;

/**
 * Import messages block.
 */
class Messages extends \Magento\Backend\Test\Block\Messages
{
    /**
     * CSS selector for import message.
     *
     * @var string
     */
    private $importResultMessage = '.messages';

    /**
     * Magento loader.
     *
     * @var string
     */
    private $loader = '[data-role="loader"]';

    /**
     * Get import result message.
     *
     * @return string
     */
    public function getImportResultMessage()
    {
        $this->waitForElementNotVisible($this->loader);

        return $this->_rootElement->find($this->importResultMessage)->getText();
    }
}
