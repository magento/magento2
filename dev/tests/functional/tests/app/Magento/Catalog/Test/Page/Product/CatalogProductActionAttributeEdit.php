<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Page\Product;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 * Class CatalogProductActionAttributeEdit
 *
 */
class CatalogProductActionAttributeEdit extends Page
{
    /**
     * URL for product creation
     */
    const MCA = 'catalog/product_action_attribute/edit';

    /**
     * CSS selector for attributes form block
     *
     * @var string
     */
    protected $attributesFormBlock = 'body';

    /**
     * Retrieve attributes form block
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action\Attribute
     */
    public function getAttributesBlockForm()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditActionAttribute(
            $this->_browser->find($this->attributesFormBlock, Locator::SELECTOR_CSS)
        );
    }
}
