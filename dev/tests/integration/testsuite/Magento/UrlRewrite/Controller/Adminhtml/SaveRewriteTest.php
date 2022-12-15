<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class SaveRewriteTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
            $this->containsEqual(__(sprintf(
                'URL key "%s" matches a reserved endpoint name (%s). Use another URL key.',
                $requestPath,
                $reservedWords
            ))),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
