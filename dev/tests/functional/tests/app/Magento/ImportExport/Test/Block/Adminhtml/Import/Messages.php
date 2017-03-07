<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import;

use Magento\Mtf\Block\Block;

/**
 * Global messages block.
 */
class Messages extends Block
{
    /**
     * CSS selector for notice message block.
     *
     * @var string
     */
    private $noticeMessage = '.message-notice';

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
     * CSS selector for error message block.
     *
     * @var string
     */
    private $errorMessage = '.message-error';

    /**
     * Get error message text.
     *
     * @return string|bool
     */
    public function getErrorMessage()
    {
        $element = $this->_rootElement->find($this->errorMessage);

        if (!$element->isVisible()) {
            return false;
        }

        return (string) $this->_rootElement->find($this->errorMessage)->getText();
    }

    /**
     * Get notice message text.
     *
     * @return string|bool
     */
    public function getNoticeMessage()
    {
        $element = $this->_rootElement->find($this->noticeMessage);

        if (!$element->isVisible()) {
            return false;
        }

        return (string) $this->_rootElement->find($this->noticeMessage)->getText();
    }

    /**
     * Get import result message.
     *
     * @return string
     */
    public function getImportResultMessage()
    {
        $this->waitForElementNotVisible($this->loader);

        return (string) $this->_rootElement->find($this->importResultMessage)->getText();
    }
}
