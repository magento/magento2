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
 * @magentoDbIsolation enabled
 */
class DeleteTest extends AbstractBackendController
{
    /**
     * @return void
     */
    public function testWithError(): void
    {
        $incorrectId = 825852;
        $postData = ['id' => $incorrectId];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/category/delete');
        $this->assertSessionMessages(
            $this->equalTo([(string)__(sprintf('No such entity with id = %s', $incorrectId))]),
            MessageInterface::TYPE_ERROR
        );
    }
}
