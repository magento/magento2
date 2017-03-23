<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class SitemapPageActions
 * Backend sitemap form page actions
 */
class SitemapPageActions extends FormPageActions
{
    /**
     * "Save & Generate" button
     *
     * @var string
     */
    protected $saveAndGenerateButton = '#generate';

    /**
     * Click on "Save & Generate" button
     *
     * @return void
     */
    public function saveAndGenerate()
    {
        $this->_rootElement->find($this->saveAndGenerateButton)->click();
    }
}
