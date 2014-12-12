<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Mtf\Fixture\InjectableFixture;
use Mtf\TestStep\TestStepInterface;

/**
 * Save product step.
 */
class SaveProductStep implements TestStepInterface
{
    /**
     * Product fixture.
     *
     * @var InjectableFixture
     */
    protected $product;

    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * @constructor
     * @param InjectableFixture $product
     * @param CatalogProductEdit $catalogProductEdit
     */
    public function __construct(InjectableFixture $product, CatalogProductEdit $catalogProductEdit)
    {
        $this->product = $product;
        $this->catalogProductEdit = $catalogProductEdit;
    }

    /**
     * Save product.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductEdit->getFormPageActions()->save($this->product);
    }
}
