<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * @constructor
     * @param CatalogProductAttribute $attribute
     * @param ObjectManager $objectManager
     * @param CatalogProductEdit $catalogProductEdit
     */
    public function __construct(
        CatalogProductAttribute $attribute,
        ObjectManager $objectManager,
        CatalogProductEdit $catalogProductEdit
    ) {
        $this->attribute = $attribute;
        $this->objectManager = $objectManager;
        $this->catalogProductEdit = $catalogProductEdit;
    }

    /**
     * Click "Save" button on attribute form.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductEdit->getNewAttributeModal()->saveAttribute();
    }

    /**
     * Delete attribute after test.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\DeleteAttributeStep::class,
            ['attribute' => $this->attribute]
        )->run();
    }
}
