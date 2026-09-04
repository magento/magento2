<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\Attributes\DataProvider;

class AsynchronousSchemaRequestProcessorTest extends AbstractController
{
    /**
     * Test that the rest controller returns the correct async schema response.
     *
     * @param string $path
     * @magentoAppArea webapi_rest
     */
    #[DataProvider('schemaRequestProvider')]
    public function testSchemaRequest($path)
    {
        ob_start();
        $this->dispatch($path);
        ob_end_clean();
        $schema = $this->getResponse()->getBody();

        // Check that an HTTP 202 response is visible for what is normally an HTTP 200.
        $this->assertMatchesRegularExpression('/202 Accepted/', $schema);

        // Make sure that the async interface definition is included in the response.
        $this->assertMatchesRegularExpression('/asynchronous-operations-data-async-response-interface/', $schema);
    }

    /**
     * Response getter
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_objectManager->get(\Magento\Framework\Webapi\Rest\Response::class);
        }
        return $this->_response;
    }

    /**
     * @return array
     */
    public static function schemaRequestProvider()
    {
        return [
            ['rest/async/schema'],
            ['rest/async/schema?services=all'],
            ['rest/all/async/schema?services=all'],
            ['rest/default/async/schema?services=all'],
            ['rest/async/schema?services=all'],
        ];
    }
}
