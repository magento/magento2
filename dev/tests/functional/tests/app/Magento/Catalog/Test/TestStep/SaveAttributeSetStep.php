<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Save attributeSet on attribute set page.
 */
class SaveAttributeSetStep implements TestStepInterface
{
    /**
     * Catalog ProductSet Edit page.
     *
     * @var CatalogProductSetEdit
     */
    protected $catalogProductSetEdit;

    /**
     * @constructor
     * @param CatalogProductSetEdit $catalogProductSetEdit
     */
    public function __construct(CatalogProductSetEdit $catalogProductSetEdit)
    {
        $this->catalogProductSetEdit = $catalogProductSetEdit;
    }

    /**
     * Save attributeSet on attribute set page.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductSetEdit->getPageActions()->save();
    }
}
