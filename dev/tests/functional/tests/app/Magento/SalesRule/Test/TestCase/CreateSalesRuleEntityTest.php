<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\PromoQuoteForm;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons\Grid;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Precondition:
 * 1. 2 sub categories in Default Category are created.
 * 2. 2 simple products are created and assigned to different subcategories by one for each.
 * 3. Default customer are created.
 *
 * Steps:
 * 1. Login to backend as admin.
 * 2. Navigate to MARKETING > Cart Price Rule.
 * 3. Create Cart Price rule according to dataset and click "Save" button.
 * 4. Perform asserts.
 *
 * @group Shopping_Cart_Price_Rules_(CS)
 * @ZephyrId MAGETWO-24855
 */
class CreateSalesRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Page PromoQuoteNew.
     *
     * @var PromoQuoteNew
     */
    protected $promoQuoteNew;

    /**
     * Page PromoQuoteEdit.
     *
     * @var PromoQuoteEdit
     */
    protected $promoQuoteEdit;

    /**
     * Page PromoQuoteIndex.
     *
     * @var PromoQuoteIndex
     */
    protected $promoQuoteIndex;

    /**
     * Sales rule name.
     *
     * @var string
     */
    protected $salesRuleName;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages.
     *
     * @param PromoQuoteNew $promoQuoteNew
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        PromoQuoteNew $promoQuoteNew,
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->promoQuoteNew = $promoQuoteNew;
        $this->promoQuoteIndex = $promoQuoteIndex;
        $this->promoQuoteEdit = $promoQuoteEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create Sales Price Rule.
     *
     * @param SalesRule $salesRule
     * @param CatalogProductSimple $productForSalesRule1
     * @param CatalogProductSimple|null $productForSalesRule2
     * @param Customer|null $customer
     * @param string|null $conditionEntity
     * @param array|null $generateCouponsSettings
     *
     * @return array
     */
    public function testCreateSalesRule(
        SalesRule $salesRule,
        CatalogProductSimple $productForSalesRule1,
        CatalogProductSimple $productForSalesRule2 = null,
        Customer $customer = null,
        $conditionEntity = null,
        array $generateCouponsSettings = null
    ) {
        $replace = null;
        $generatedCouponCodes = null;
        $this->salesRuleName = $salesRule->getName();

        // Prepare data
        if ($customer !== null) {
            $customer->persist();
        }
        $productForSalesRule1->persist();
        if ($productForSalesRule2 !== null) {
            $productForSalesRule2->persist();
            if ($conditionEntity !== null) {
                $replace = $this->prepareCondition($productForSalesRule2, $conditionEntity);
            }
        }

        // Steps
        $this->promoQuoteNew->open();
        $this->promoQuoteNew->getSalesRuleForm()->fill($salesRule, null, $replace);

        if (
            ($salesRule->getUseAutoGeneration() == 'Yes' || $salesRule->getCouponType() == "Auto")
            && !empty($generateCouponsSettings)
        ) {
            $this->promoQuoteNew->getFormPageActions()->saveAndContinue();

            /** @var PromoQuoteForm $salesRuleForm */
            $salesRuleForm = $this->promoQuoteNew->getSalesRuleForm();
            $salesRuleForm->generateCoupons($generateCouponsSettings);

            /** @var BlockPromoSalesRuleEditTabCoupons $manageCouponCodesSection */
            $manageCouponCodesSection = $salesRuleForm->getSection('block_promo_sales_rule_edit_tab_coupons');

            /** @var Grid $couponGrid */
            $couponGrid = $manageCouponCodesSection->getCouponGrid();

            /** @var array $generatedCouponCodes */
            $generatedCouponCodes = $couponGrid->getCouponCodes();
        } else {
            $this->promoQuoteNew->getFormPageActions()->save();
        }

        return ['salesRule' => $salesRule, 'couponCodes' => $generatedCouponCodes];
    }

    /**
     * Prepare condition for Sales rule.
     *
     * @param CatalogProductSimple $productSimple
     * @param string $conditionEntity
     * @return array
     */
    protected function prepareCondition(CatalogProductSimple $productSimple, $conditionEntity)
    {
        $result = [];

        switch ($conditionEntity) {
            case 'category':
                $result['%category_id%'] = $productSimple->getDataFieldConfig('category_ids')['source']->getIds()[0];
                break;
            case 'attribute':
                /** @var \Magento\Catalog\Test\Fixture\CatalogProductAttribute[] $attrs */
                $attributes = $productSimple->getDataFieldConfig('attribute_set_id')['source']
                    ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']->getAttributes();

                $result['%attribute_name%'] = $attributes[0]->getFrontendLabel();
                $result['%attribute_value%'] = $attributes[0]->getOptions()[0]['view'];
                break;
        }

        return [
            'conditions' => [
                'conditions_serialized' => $result,
            ],
        ];
    }

    /**
     * Delete current sales rule.
     *
     * @return void
     */
    public function tearDown()
    {
        $filter = [
            'name' => $this->salesRuleName,
        ];

        $this->promoQuoteIndex->open();
        $this->promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen($filter);
        $this->promoQuoteEdit->getFormPageActions()->delete();
        $this->promoQuoteEdit->getModalBlock()->acceptAlert();
    }
}
