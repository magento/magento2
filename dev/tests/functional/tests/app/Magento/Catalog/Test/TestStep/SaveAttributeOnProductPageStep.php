<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Click "Save" button on attribute form on product page.
 */
class SaveAttributeOnProductPageStep implements TestStepInterface
{
    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Product attribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Object manager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @constructor
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductAttribute $attribute
     * @param ObjectManager $objectManager
     */
    public function __construct(
        CatalogProductEdit $catalogProductEdit,
        CatalogProductAttribute $attribute,
        ObjectManager $objectManager
    ) {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->attribute = $attribute;
        $this->objectManager = $objectManager;
    }

    /**
     * Click "Save" button on attribute form on product page.
     *
     * @return array
     */
    public function run()
    {
        $this->catalogProductEdit->getProductForm()->saveAttributeForm();
    }

    /**
     * Delete attribute after test.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\DeleteAttributeStep',
            ['attribute' => $this->attribute]
        )->run();
    }
}
