<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Several Cart Price Rules are created.
 * 2. Create sales rule from dataset using Handler.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to MARKETING > Cart Price Rules.
 * 3. Open from grid test Rule.
 * 4. Click 'Delete' button.
 * 5. Perform asserts.
 *
 * @group Shopping_Cart_Price_Rules
 * @ZephyrId MAGETWO-24985
 */
class DeleteSalesRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

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
     * Inject pages.
     *
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     */
    public function __inject(
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit
    ) {
        $this->promoQuoteIndex = $promoQuoteIndex;
        $this->promoQuoteEdit = $promoQuoteEdit;
    }

    /**
     * Delete Sales Rule Entity.
     *
     * @param SalesRule $salesRule
     * @param CatalogProductSimple $productForSalesRule1
     * @return void
     */
    public function test(SalesRule $salesRule, CatalogProductSimple $productForSalesRule1 = null)
    {
        // Preconditions
        $salesRule->persist();

        if ($productForSalesRule1) {
            $productForSalesRule1->persist();
        }

        // Steps
        $this->promoQuoteIndex->open();
        $this->promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen(['name' => $salesRule->getName()]);
        $this->promoQuoteEdit->getFormPageActions()->delete();
        $this->promoQuoteEdit->getModalBlock()->acceptAlert();
    }
}
