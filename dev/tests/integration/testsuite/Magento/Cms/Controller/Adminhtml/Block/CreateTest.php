<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class CreateTest extends AbstractBackendController
{
    /**
     * Test create CMS block with invalid URL
     *
     * @return void
     */
    public function testCreateBlockWithInvalidUrl(): void
    {
        $identifier = 'admin';
        $reservedWords = 'admin, soap, rest, graphql, standard';
        $sessionMessages = [sprintf(
            'URL key &quot;%s&quot; matches a reserved endpoint name (%s). Use another URL key.',
            $identifier,
            $reservedWords
        )];
        $requestData = [
            'title' => 'block title',
            'identifier' => $identifier,
            'content' => '',
            'is_active' => 1,
        ];

        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch('backend/cms/block/save');
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );
    }
}
