<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * CSS selector for validation errors list block.
     *
     * @var string
     */
    private $validationErrorList = '.import-error-list';

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
