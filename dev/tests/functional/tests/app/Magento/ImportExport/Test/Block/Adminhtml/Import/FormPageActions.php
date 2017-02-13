<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Form page actions block.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Save" button.
     *
     * @var string
     */
    protected $saveButton = '#upload_button';

    /**
     * Click "Check Data" button.
     * @return void
     */
    public function clickCheckData()
    {
        $this->save();
    }
}
