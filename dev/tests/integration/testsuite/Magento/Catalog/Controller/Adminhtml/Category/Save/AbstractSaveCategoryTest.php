<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Abstract save category.
 */
class AbstractSaveCategoryTest extends AbstractBackendController
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->jsonSerializer = $this->_objectManager->get(Json::class);
    }

    /**
     * Perform save category request with category POST data.
     *
     * @param array $data
     * @return array
     */
    protected function performSaveCategoryRequest(array $data): array
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data);
        $this->dispatch('backend/catalog/category/save');

        return $this->jsonSerializer->unserialize($this->getResponse()->getBody());
    }

    /**
     * Assert that session has message about successfully category save.
     *
     * @param array $responseData
     * @return void
     */
    protected function assertRequestIsSuccessfullyPerformed(array $responseData): void
    {
        $this->assertTrue(isset($responseData['category']['entity_id']));
        $this->assertFalse($responseData['error'], 'Response message: ' . $responseData['messages']);
        $message = str_replace('.', '\.', (string)__('You saved the category.'));
        $this->assertRegExp("/>{$message}</", $responseData['messages']);
    }
}
