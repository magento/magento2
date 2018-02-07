<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
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
     * Bundle options locator.
     *
     * @var string
     */
    protected $bundleOptions = './/*[@data-index="bundle_options"]/tbody';

    /**
     * Open option section.
     *
     * @var string
     */
    protected $openOption = './tr[%d]//*[@data-role="collapsible-title"]';

    /**
     * Selector for option content.
     *
     * @var string
     */
    protected $optionContent = './tr[%d]//*[@data-role="collapsible-content"]';

    /**
     * Locator for bundle option row.
     *
     * @var string
     */
    protected $bundleOptionRow = './tr[%d]';

    /**
     * Get bundle options block.
     *
     * @param int $rowNumber
     * @param ElementInterface $element
     * @return Option
     */
    private function getBundleOptionBlock($rowNumber, ElementInterface $element)
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option',
            [
                'element' => $element->find(sprintf($this->bundleOptionRow, $rowNumber), Locator::SELECTOR_XPATH)
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
        $context = $this->_rootElement->find($this->bundleOptions, Locator::SELECTOR_XPATH);
        foreach ($fields['bundle_selections']['value']['bundle_options'] as $key => $bundleOption) {
            $count = $key + 1;
            $itemOption = $context->find(sprintf($this->openOption, $count), Locator::SELECTOR_XPATH);
            $isContent = $context->find(sprintf($this->optionContent, $count), Locator::SELECTOR_XPATH)->isVisible();
            if ($itemOption->isVisible() && !$isContent) {
                $itemOption->click();
            } elseif (!$itemOption->isVisible()) {
                $this->_rootElement->find($this->addNewOption)->click();
            }
            $this->getBundleOptionBlock($count, $context)->fillOption($bundleOption);
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
        $index = 1;
        $context = $this->_rootElement->find($this->bundleOptions, Locator::SELECTOR_XPATH);
        foreach ($fields['bundle_selections']['value']['bundle_options'] as $key => &$bundleOption) {
            if (!$context->find(sprintf($this->optionContent, $index), Locator::SELECTOR_XPATH)->isVisible()) {
                $context->find(sprintf($this->openOption, $index), Locator::SELECTOR_XPATH)->click();
            }
            foreach ($bundleOption['assigned_products'] as &$product) {
                $product['data']['getProductName'] = $product['search_data']['name'];
            }
            $newFields['bundle_selections'][$key] =
                $this->getBundleOptionBlock($index, $context)->getOptionData($bundleOption);
            $index++;
        }

        return $newFields;
    }
}
