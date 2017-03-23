<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing\OptionTier;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;

/**
 * Product advanced pricing section.
 */
class AdvancedPricing extends Section
{
    /**
     * Locator for Advanced Pricing modal.
     *
     * @var string
     */
    protected $advancedPricingRootElement = '.product_form_product_form_advanced_pricing_modal';

    /**
     * Locator for Tier Price block.
     *
     * @var string
     */
    protected $tierPrice = 'div[data-index="tier_price"]';

    /**
     * Selector for "Done" button.
     *
     * @var string
     */
    protected $doneButton = '.action-primary[data-role="action"]';

    /**
     * Fill 'Advanced price' product form on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $contextElement
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $contextElement = null)
    {
        $context = $this->browser->find($this->advancedPricingRootElement);
        if (isset($fields['tier_price'])) {
            /** @var AbstractOptions $optionsForm */
            $optionsForm = $this->getTierPriceForm($context);
            $optionsForm->fillOptions($fields['tier_price'], $context->find('div[data-index="tier_price"]'));
            unset($fields['tier_price']);
        }
        $data = $this->dataMapping($fields);
        $this->_fill($data, $context);
        $context->find($this->doneButton)->click();

        return $this;
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $formData = [];
        $context = $this->browser->find($this->advancedPricingRootElement);
        if (isset($fields['tier_price'])) {
            /** @var AbstractOptions $optionsForm */
            $optionsForm = $this->getTierPriceForm($context);
            $formData['tier_price'] = $optionsForm->getDataOptions(
                $fields['tier_price'],
                $context->find('div[data-index="tier_price"]')
            );
            unset($fields['tier_price']);
        }
        $data = $this->dataMapping($fields);
        $formData += $this->_getData($data, $context);
        $context->find($this->doneButton)->click();

        return $formData;
    }

    /**
     * Get Tier Price block.
     *
     * @param SimpleElement|null $element
     * @return OptionTier
     */
    public function getTierPriceForm(SimpleElement $element = null)
    {
        $element = $element ?: $this->browser->find($this->advancedPricingRootElement);
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing\OptionTier::class,
            ['element' => $element]
        );
    }
}
