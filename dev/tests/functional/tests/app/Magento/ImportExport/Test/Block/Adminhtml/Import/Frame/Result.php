<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import\Frame;

use Magento\Mtf\Block\Block;

/**
 * Import checking result data block.
 */
class Result extends Block
{
    /**
     * CSS selector for import button.
     *
     * @var string
     */
    private $importButton = 'div > .success > div > button';

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
     * CSS selector for validation errors list block.
     *
     * @var string
     */
    private $validationErrorList = '.import-error-list';

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
     * Click import button.
     *
     * @return void
     */
    public function clickImportButton()
    {
        $this->_rootElement->find($this->importButton)->click();
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

    /**
     * Get errors messages list.
     *
     * @return array|false
     */
    public function getErrorsList()
    {
        $element = $this->_rootElement->find($this->validationErrorList);

        if (!$element->isVisible()) {
            return false;
        }

        $text = $this->_rootElement->find($this->validationErrorList)->getText();

        return (array) explode(PHP_EOL, strip_tags($text));
    }
}
