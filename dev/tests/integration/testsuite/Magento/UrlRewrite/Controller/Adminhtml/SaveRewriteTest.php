<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Controller\Adminhtml;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class SaveRewriteTest extends AbstractBackendController
{
    /**
     * Test create url rewrite with invalid target path
     *
     * @return void
     */
    public function testSaveRewriteWithInvalidRequestPath() : void
    {
        $requestPath = 'admin';
        $reservedWords = 'admin, soap, rest, graphql, standard';
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'description' => 'Some URL rewrite description',
                'options' => 'R',
                'request_path' => 'admin',
                'target_path' => "target_path",
                'store_id' => 1,
            ]
        );
        $this->dispatch('backend/admin/url_rewrite/save');

        $this->assertSessionMessages(
            $this->containsEqual(__(
                'URL key "%1" matches a reserved endpoint name (%2). Use another URL key.',
                $requestPath,
                $reservedWords
            )),
            MessageInterface::TYPE_ERROR
        );
    }
}
