<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Integration;

use Magento\Framework\App\Response\Http;
use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Laminas\Http\Headers;

/**
 * Validates the headers for Graphql CORS requests
 */
class CorsGraphQlTest extends ControllerTestCase
{
    /**
     * Prepares HTTP headers
     *
     * @param string $origin
     * @return Headers
     */
    private function getHeadersForGraphQlRequest($origin = 'https://example.com'): Headers
    {
        $httpHeaders = new Headers();
        $httpHeaders->addHeaders([
            'Origin' => $origin,
            'Content-Type' => 'application/json'
        ]);
        return $httpHeaders;
    }

    /**
     * Returns GraphQl query string
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
 query{
  products (search: "test", pageSize: 2){
    items{
      ... on SimpleProduct {
        name
      }
    }
  }
}
QUERY;
    }

    /**
     * Makes the GraphQl request
     *
     * @param string $origin
     * @return void
     */
    private function addOriginAndSendGraphQlRequest(string $origin): void
    {
        $this->getRequest()->setMethod('POST')
            ->setHeaders($this->getHeadersForGraphQlRequest($origin))
            ->setContent($this->getQuery());
        $this->dispatch('/graphql');
    }

    /**
     * @magentoConfigFixture default/web/graphql/cors_allowed_origins https://www.example.com
     * @magentoConfigFixture default/web/graphql/cors_allowed_headers Content-Type
     * @magentoConfigFixture default/web/graphql/cors_allowed_methods GET,POST,OPTIONS
     * @magentoConfigFixture default/web/graphql/cors_max_age 86400
     * @magentoConfigFixture default/web/graphql/cors_allow_credentials 1
     */
    public function testIsCorsHeadersPresentInGraphQlResponse()
    {
        $this->addOriginAndSendGraphQlRequest("https://www.example.com");
        $response = $this->getResponse();
        $this->assertNotFalse($response->getHeader('Access-Control-Allow-Origin'));
        $this->assertNotFalse($response->getHeader('Access-Control-Allow-Headers'));
        $this->assertNotFalse($response->getHeader('Access-Control-Allow-Methods'));
        $this->assertNotFalse($response->getHeader('Access-Control-Max-Age'));
    }

    /**
     * @magentoConfigFixture default/web/graphql/cors_allowed_origins https://www.example.com
     * @magentoConfigFixture default/web/graphql/cors_allowed_headers Content-Type
     * @magentoConfigFixture default/web/graphql/cors_allowed_methods GET,POST,OPTIONS
     * @magentoConfigFixture default/web/graphql/cors_max_age 86400
     * @magentoConfigFixture default/web/graphql/cors_allow_credentials 1
     */
    public function testNormalRequestDoesNotContainsCorsHeaders()
    {
        $httpHeaders = new Headers();
        $httpHeaders->addHeaderLine('Origin: https://www.example.com');
        $this->dispatch('/');

        $response = $this->getResponse();
        $this->assertFalse($response->getHeader('Access-Control-Allow-Origin'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Headers'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Methods'));
        $this->assertFalse($response->getHeader('Access-Control-Max-Age'));
    }

    /**
     * @magentoConfigFixture default/web/graphql/cors_allowed_origins https://www.example.com
     * @magentoConfigFixture default/web/graphql/cors_allowed_headers Content-Type
     * @magentoConfigFixture default/web/graphql/cors_allowed_methods GET,POST,OPTIONS
     * @magentoConfigFixture default/web/graphql/cors_max_age 86400
     * @magentoConfigFixture default/web/graphql/cors_allow_credentials 1
     */
    public function testCorsNotAddedIfOriginIsNotAllowed()
    {
        $this->addOriginAndSendGraphQlRequest("https://www.test.com");
        $response = $this->getResponse();
        $this->assertFalse($response->getHeader('Access-Control-Allow-Origin'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Headers'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Methods'));
        $this->assertFalse($response->getHeader('Access-Control-Max-Age'));
    }

    public function testCorsRequestFailsIfCorsConfigurationIsNotProvided()
    {
        $this->addOriginAndSendGraphQlRequest("https://www.example.com");
        $response = $this->getResponse();
        $this->assertFalse($response->getHeader('Access-Control-Allow-Origin'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Headers'));
        $this->assertFalse($response->getHeader('Access-Control-Allow-Methods'));
        $this->assertFalse($response->getHeader('Access-Control-Max-Age'));
    }
}
