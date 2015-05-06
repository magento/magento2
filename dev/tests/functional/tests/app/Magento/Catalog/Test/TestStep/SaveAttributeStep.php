<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * Delete attribute step.
     *
     * @var DeleteAttributeStep
     */
    protected $deleteAttribute;

    /**
     * @constructor
     * @param CatalogProductAttributeNew $attributeNew
     * @param DeleteAttributeStep $deleteAttribute
     */
    public function __construct(CatalogProductAttributeNew $attributeNew, DeleteAttributeStep $deleteAttribute)
    {
        $this->attributeNew = $attributeNew;
        $this->deleteAttribute = $deleteAttribute;
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
        $this->deleteAttribute->run();
    }
}
