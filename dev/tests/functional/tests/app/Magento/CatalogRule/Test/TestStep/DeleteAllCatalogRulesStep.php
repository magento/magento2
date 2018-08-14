<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestStep;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class DeleteAllCatalogRulesStep
 * Delete all Catalog Rules on backend
 */
class DeleteAllCatalogRulesStep implements TestStepInterface
{
    /**
     * Catalog rule index page
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * Catalog rule new and edit page
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * @construct
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     */
    public function __construct(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew
    ) {
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
    }

    /**
     * Delete Catalog Rule on backend
     *
     * @return array
     */
    public function run()
    {
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->resetFilter();
        while ($this->catalogRuleIndex->getCatalogRuleGrid()->isFirstRowVisible()) {
            $this->catalogRuleIndex->getCatalogRuleGrid()->openFirstRow();
            $this->catalogRuleNew->getFormPageActions()->delete();
            $this->catalogRuleNew->getModalBlock()->acceptAlert();
            $this->catalogRuleIndex->getSystemMessageDialog()->closePopup();
        }
    }
}
