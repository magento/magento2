<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

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
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * New Attribute modal locator.
     *
     * @var string
     */
    protected $newAttributeModal = '.product_form_product_form_add_attribute_modal_create_new_attribute_modal';

    /**
     * "Save" button.
     *
     * @var string
     */
    protected $save = 'button#save';

    /**
     * @constructor
     * @param CatalogProductAttribute $attribute
     * @param ObjectManager $objectManager
     * @param BrowserInterface $browser
     */
    public function __construct(
        CatalogProductAttribute $attribute,
        ObjectManager $objectManager,
        BrowserInterface $browser
    ) {
        $this->attribute = $attribute;
        $this->objectManager = $objectManager;
        $this->browser = $browser;
    }

    /**
     * Click "Save" button on attribute form.
     *
     * @return void
     */
    public function run()
    {
        $this->browser->find($this->newAttributeModal)->find($this->save)->click();
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
