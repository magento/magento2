<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Save attribute on attribute page.
 */
class SaveAttributeStep implements TestStepInterface
{
    /**
     * Catalog product attribute edit page.
     *
     * @var CatalogProductAttributeNew
     */
    protected $attributeNew;

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
     * @param CatalogProductAttributeNew $attributeNew
     * @param CatalogProductAttribute $attribute
     * @param ObjectManager $objectManager
     */
    public function __construct(
        CatalogProductAttributeNew $attributeNew,
        CatalogProductAttribute $attribute,
        ObjectManager $objectManager
    ) {
        $this->attributeNew = $attributeNew;
        $this->attribute = $attribute;
        $this->objectManager = $objectManager;
    }

    /**
     * Click "Save" button on attribute edit page.
     *
     * @return void
     */
    public function run()
    {
        $this->attributeNew->getPageActions()->save();
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
