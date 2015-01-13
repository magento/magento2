<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element;

/**
 * Class AdvancedPropertiesTab
 * Tab "Advanced Attribute Properties"
 */
class Advanced extends Tab
{
    /**
     * "Advanced Attribute Properties" tab-button
     *
     * @var string
     */
    protected $propertiesTab = '[data-target="#advanced_fieldset-content"][data-toggle="collapse"]';

    /**
     * "Advanced Attribute Properties" tab-button active
     *
     * @var string
     */
    protected $propertiesTabActive = '.title.active';

    /**
     * Fill 'Advanced Attribute Properties' tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!$this->_rootElement->find($this->propertiesTabActive)->isVisible()) {
            $this->_rootElement->find($this->propertiesTab)->click();
        }

        return parent::fillFormTab($fields);
    }
}
