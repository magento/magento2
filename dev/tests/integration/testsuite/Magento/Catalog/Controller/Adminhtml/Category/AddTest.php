<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for catalog category Add controller
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Category\Add
 *
 * @magentoAppArea adminhtml
 */
class AddTest extends AbstractBackendController
{
    /**
     * @var int
     */
    const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getRequest()->setParams([]);
    }


    /**
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testExecuteWithoutParams(): void
    {
        $this->dispatch('backend/catalog/category/add');
        $this->assertRedirect($this->stringContains('catalog/category/index'));
    }

    /**
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testExecuteAsAjax(): void
    {
        $this->getRequest()->setQueryValue('isAjax', true);
        $this->getRequest()->setParam('parent', self::DEFAULT_ROOT_CATEGORY);
        $this->dispatch('backend/catalog/category/add');
        $this->assertJson($this->getResponse()->getBody());
    }
}
