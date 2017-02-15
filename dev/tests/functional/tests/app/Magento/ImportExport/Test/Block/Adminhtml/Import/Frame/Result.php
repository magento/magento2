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
