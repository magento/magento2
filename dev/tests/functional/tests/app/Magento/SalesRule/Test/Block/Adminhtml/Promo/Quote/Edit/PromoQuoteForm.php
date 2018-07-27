<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons;

/**
 * Sales rule edit form.
 */
class PromoQuoteForm extends FormSections
{
    /**
     * Selector of element to wait for. If set by child will wait for element after action
     *
     * @var string
     */
    protected $waitForSelector = '.spinner';

    /**
     * Wait for should be for visibility or not?
     *
     * @var boolean
     */
    protected $waitForSelectorVisible = false;

    /**
     * Fill form with sections.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @param array $replace
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, array $replace = null)
    {
        $sections = $this->getFixtureFieldsByContainers($fixture);
        if ($replace) {
            $sections = $this->prepareData($sections, $replace);
        }
        $this->fillContainers($sections, $element);
    }

    /**
     * Generate coupons for Cart Rule.
     *
     * @param array $generateSettings
     *
     * @return void
     */
    public function generateCoupons(array $generateSettings)
    {
        $this->fillContainers([
            'block_promo_sales_rule_edit_tab_coupons' => $generateSettings
        ]);

        /** @var BlockPromoSalesRuleEditTabCoupons $couponSection */
        $couponSection = $this->getSection('block_promo_sales_rule_edit_tab_coupons');
        $couponSection->pressGenerateButton();
    }

    /**
     * Replace placeholders in each values of data.
     *
     * @param array $sections
     * @param array $replace
     * @return array
     */
    protected function prepareData(array $sections, array $replace)
    {
        foreach ($replace as $sectionName => $fields) {
            foreach ($fields as $key => $pairs) {
                if (isset($sections[$sectionName][$key])) {
                    $sections[$sectionName][$key]['value'] = str_replace(
                        array_keys($pairs),
                        array_values($pairs),
                        $sections[$sectionName][$key]['value']
                    );
                }
            }
        }

        return $sections;
    }
}
