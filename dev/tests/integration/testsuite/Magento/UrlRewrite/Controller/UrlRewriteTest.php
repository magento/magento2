<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller;

/**
 * Class to test Match corresponding URL Rewrite
 */
class UrlRewriteTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoAppIsolation enabled
     *
     * @covers \Magento\UrlRewrite\Controller\Router::match
     * @covers \Magento\UrlRewrite\Model\Storage\DbStorage::doFindOneByData
     *
     * @param string $request
     * @param string $redirect
     * @param int $expectedCode
     *
     * @dataProvider requestDataProvider
     */
    public function testMatchUrlRewrite(
        $request,
        $redirect,
        $expectedCode = 301
    ) {
        $this->dispatch($request);
        $response = $this->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertEquals($expectedCode, $code, 'Invalid response code');

        if ($expectedCode !== 200) {
            $location = $response->getHeader('Location')->getFieldValue();
            $this->assertStringEndsWith(
                $redirect,
                $location,
                'Invalid location header'
            );
        }
    }

    /**
     * Data provider for testMatchUrlRewrite
     *
     * @return array
     */
    public function requestDataProvider(): array
    {
        return [
            'Use Case #1: Rewrite: page-one/ --(301)--> page-a/; Request: page-one/ --(301)--> page-a/' => [
                'request' => '/page-one/',
                'redirect' => '/page-a/',
            ],
            'Use Case #2: Rewrite: page-one/ --(301)--> page-a/; Request: page-one --(301)--> page-a/' => [
                'request' => '/page-one',
                'redirect' => '/page-a/',
            ],
            'Use Case #3: Rewrite: page-two --(301)--> page-b; Request: page-two --(301)--> page-b' => [
                'request' => '/page-two',
                'redirect' => '/page-b',
            ],
            'Use Case #4: Rewrite: page-two --(301)--> page-b; Request: page-two --(301)--> page-b' => [
                'request' => '/page-two/',
                'redirect' => '/page-b',
            ],
            'Use Case #5: Rewrite: page-similar --(301)--> page-a; Request: page-similar --(301)--> page-a' => [
                'request' => '/page-similar',
                'redirect' => '/page-a',
            ],
            'Use Case #6: Rewrite: page-similar/ --(301)--> page-b; Request: page-similar/ --(301)--> page-b' => [
                'request' => '/page-similar/',
                'redirect' => '/page-b',
            ],
            'Use Case #7: Request with query params' => [
                'request' => '/enable-cookies/?test-param',
                'redirect' => '',
                200,
            ],
        ];
    }
}
