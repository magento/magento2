<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option;
use Magento\Mtf\Client\Element;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Bundle options section block on product-details section.
 */
class Bundle extends Section
{
    /**
     * Selector for 'New Option' button.
     *
     * @var string
     */
    protected $addNewOption = 'button[data-index="add_button"]';

    /**
     * Open option section.
     *
     * @var string
     */
    protected $openOption = '[data-index="bundle_options"] tbody tr:nth-child(%d) [data-role="collapsible-title"]';

    /**
     * Selector for option content.
     *
     * @var string
     */
    protected $optionContent = '[data-index="bundle_options"] tbody tr:nth-child(%d) [data-role="collapsible-content"]';

    /**
     * Get bundle options block.
     *
     * @param int $blockNumber
     * @return Option
     */
    protected function getBundleOptionBlock($blockNumber)
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option',
            [
                'element' => $this->_rootElement->find(
                    sprintf('[data-index="bundle_options"] tbody tr:nth-child(%d)', $blockNumber)
                )
            ]
        );
    }

    /**
     * Fill bundle options.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        if (!isset($fields['bundle_selections'])) {
            return $this;
        }
        foreach ($fields['bundle_selections']['value']['bundle_options'] as $key => $bundleOption) {
            $count = $key + 1;
            $itemOption = $this->_rootElement->find(sprintf($this->openOption, $count));
            $isContent = $this->_rootElement->find(sprintf($this->optionContent, $count))->isVisible();
            if ($itemOption->isVisible() && !$isContent) {
                $itemOption->click();
            } elseif (!$itemOption->isVisible()) {
                $this->_rootElement->find($this->addNewOption)->click();
            }
            $this->getBundleOptionBlock($count)->fillOption($bundleOption);
        }
        return $this;
    }

    /**
     * Get data to fields on downloadable tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $newFields = [];
        if (!isset($fields['bundle_selections'])) {
            return $this;
        }
        $index = 0;
        foreach ($fields['bundle_selections']['value']['bundle_options'] as $key => &$bundleOption) {
            if (!$this->_rootElement->find(sprintf($this->optionContent, $key))->isVisible()) {
                $this->_rootElement->find(sprintf($this->openOption, $index))->click();
            }
            foreach ($bundleOption['assigned_products'] as &$product) {
                $product['data']['getProductName'] = $product['search_data']['name'];
            }
            $newFields['bundle_selections'][$key] = $this->getBundleOptionBlock($key)->getOptionData($bundleOption);
            $index++;
        }

        return $newFields;
    }
}
