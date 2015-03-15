<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;

/**
 * Tab "Advanced Attribute Properties".
 */
class Advanced extends Tab
{
    /**
     * "Advanced Attribute Properties" tab-button.
     *
     * @var string
     */
    protected $propertiesTab = '[data-target="#advanced_fieldset-content"][data-toggle="collapse"]';

    /**
     * "Advanced Attribute Properties" content.
     *
     * @var string
     */
    protected $propertiesTabContent = '#advanced_fieldset-content';

    /**
     * Fill 'Advanced Attribute Properties' tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        if (!$this->_rootElement->find($this->propertiesTabContent)->isVisible()) {
            $this->_rootElement->find($this->propertiesTab)->click();
        }

        return parent::fillFormTab($fields, $element);
    }
}
