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
     * Get error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        if (!$this->_rootElement->find($this->errorMessage)->isVisible()) {
            return false;
        }
        return parent::getErrorMessage();
    }

    /**
     * Get notice message.
     *
     * @return array
     */
    public function getNoticeMessage()
    {
        if (!$this->_rootElement->find($this->noticeMessage)->isVisible()) {
            return false;
        }
        return parent::getNoticeMessage();
    }

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
