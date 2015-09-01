<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;

/**
 * Integration form block.
 */
class IntegrationForm extends FormTabs
{
    /**
     * Get array of label => js error text.
     *
     * @param string $tabName
     * @return array
     */
    public function getJsErrors($tabName)
    {
        $tab = $this->getTab($tabName);
        $this->openTab($tabName);
        return $tab->getJsErrors();
    }
}
