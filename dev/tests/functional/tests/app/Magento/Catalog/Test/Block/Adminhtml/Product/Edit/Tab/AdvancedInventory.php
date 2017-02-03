<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Advanced inventory tab.
 */
class AdvancedInventory extends ProductTab
{
    /**
     * Styled field block selector.
     *
     * @var string
     */
    protected $styledFieldBlock = './/*[@id="table_cataloginventory"]/div[@style][1]';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $this->waitInit();
        return parent::fillFormTab($fields, $element);
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $this->waitInit();
        return parent::getDataFormTab($fields, $element);
    }

    /**
     * Wait until init tab.
     *
     * @return void
     */
    public function waitInit()
    {
        $context = $this->_rootElement;
        $selector = $this->styledFieldBlock;

        $context->waitUntil(
            function () use ($context, $selector) {
                $elements = $context->getElements($selector, Locator::SELECTOR_XPATH);
                return count($elements) > 0 ? true : null;
            }
        );
    }
}
