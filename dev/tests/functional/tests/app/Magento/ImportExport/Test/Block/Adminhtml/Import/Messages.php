<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import;

/**
 * Import messages block.
 */
class Messages extends \Magento\Backend\Test\Block\Messages
{
    /**
     * CSS selector for error message block.
     *
     * @var string
     */
    protected $errorMessage = '.message-error';

    /**
     * CSS selector for validation errors list block.
     *
     * @var string
     */
    private $validationErrorList = '.import-error-list';

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

    /**
     * Get error message.
     *
     * @return bool|string
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
     * @return bool|string
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
