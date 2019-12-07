<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;

/**
 * Delete specified Sales Rules on backend.
 */
class DeleteSalesRulesStep implements TestStepInterface
{
    /**
     * Promo Quote index page.
     *
     * @var PromoQuoteIndex
     */
    private $promoQuoteIndex;

    /**
     * Promo Quote edit page.
     *
     * @var PromoQuoteEdit
     */
    private $promoQuoteEdit;

    /**
     * Sales Rules to delete.
     *
     * @var array
     */
    private $salesRules;

    /**
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @param array $salesRules
     */
    public function __construct(
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit,
        array $salesRules
    ) {
        $this->promoQuoteIndex = $promoQuoteIndex;
        $this->promoQuoteEdit = $promoQuoteEdit;
        $this->salesRules = $salesRules;
    }

    /**
     * Delete Sales Rules on backend.
     *
     * @return void
     */
    public function run()
    {
        $this->promoQuoteIndex->open();
        foreach ($this->salesRules as $salesRuleName) {
            $filter = ['name' => $salesRuleName];
            $this->promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen($filter);
            $this->promoQuoteEdit->getFormPageActions()->delete();
            $this->promoQuoteEdit->getModalBlock()->acceptAlert();
        }
    }
}
