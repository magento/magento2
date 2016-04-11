<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

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
     * Inject pages.
     *
     * @param PromoQuoteNew $promoQuoteNew
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @return void
     */
    public function __inject(
        PromoQuoteNew $promoQuoteNew,
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit
    ) {
        $this->promoQuoteNew = $promoQuoteNew;
        $this->promoQuoteIndex = $promoQuoteIndex;
        $this->promoQuoteEdit = $promoQuoteEdit;
    }

    /**
     * Create customer and 2 simple products with categories before run test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customer', ['dataset' => 'default']);
        $customer->persist();

        $productForSalesRule1 = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataset' => 'simple_for_salesrule_1']
        );
        $productForSalesRule1->persist();

        $productForSalesRule2 = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataset' => 'simple_for_salesrule_2']
        );
        $productForSalesRule2->persist();

        return [
            'customer' => $customer,
            'productForSalesRule1' => $productForSalesRule1,
            'productForSalesRule2' => $productForSalesRule2
        ];
    }

    /**
     * Create Sales Rule Entity.
     *
     * @param SalesRule $salesRule
     * @return void
     */
    public function testCreateSalesRule(SalesRule $salesRule)
    {
        // Preconditions
        $this->salesRuleName = $salesRule->getName();

        // Steps
        $this->promoQuoteNew->open();
        $this->promoQuoteNew->getSalesRuleForm()->fill($salesRule);
        $this->promoQuoteNew->getFormPageActions()->save();
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
