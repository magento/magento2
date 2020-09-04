<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for class \Magento\Catalog\Controller\Adminhtml\Category\Delete
 *
 * @magentoAppArea adminhtml
 */
class DeleteTest extends AbstractBackendController
{
    /**
     * @return void
     */
    public function testDeleteMissingCategory(): void
    {
        $incorrectId = 825852;
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['id' => $incorrectId]);
        $this->dispatch('backend/catalog/category/delete');
        $this->assertSessionMessages(
            $this->equalTo([(string)__(sprintf('No such entity with id = %s', $incorrectId))]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testDeleteCategory(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['id' => 333]);
        $this->dispatch('backend/catalog/category/delete');
        $this->assertSessionMessages($this->equalTo([(string)__('You deleted the category.')]));
    }
}
