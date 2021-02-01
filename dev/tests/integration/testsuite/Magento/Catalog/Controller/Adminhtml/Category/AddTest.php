<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for catalog category Add controller
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Category\Add
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AddTest extends AbstractBackendController
{
    /**
     * @var DefaultCategory
     */
    private $defaultCategoryHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultCategoryHelper = $this->_objectManager->get(DefaultCategory::class);
    }

    /**
     * @return void
     */
    public function testExecuteWithoutParams(): void
    {
        $this->dispatch('backend/catalog/category/add');
        $this->assertRedirect($this->stringContains('catalog/category/index'));
    }

    /**
     * @return void
     */
    public function testExecuteAsAjax(): void
    {
        $this->getRequest()->setQueryValue('isAjax', true);
        $this->getRequest()->setParam('parent', $this->defaultCategoryHelper->getId());
        $this->dispatch('backend/catalog/category/add');
        $this->assertJson($this->getResponse()->getBody());
    }
}
