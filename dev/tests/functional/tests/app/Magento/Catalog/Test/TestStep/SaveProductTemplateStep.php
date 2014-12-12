<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Mtf\TestStep\TestStepInterface;

/**
 * Save attributeSet on attribute set page.
 */
class SaveProductTemplateStep implements TestStepInterface
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
