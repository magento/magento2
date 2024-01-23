<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for catalog category grid controller
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Category\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends AbstractBackendController
{
    /**
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testRenderCategoryGrid(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/category/grid');
        $content = $this->getResponse()->getBody();
        $this->assertEquals(1, Xpath::getElementsCountForXpath('//div[@id="catalog_category_products"]', $content));
    }
}
