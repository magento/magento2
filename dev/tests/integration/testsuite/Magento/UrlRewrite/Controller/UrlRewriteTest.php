<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Controller;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * Class to test Match corresponding URL Rewrite
 */
class UrlRewriteTest extends AbstractController
{
    /**
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDbIsolation disabled
     *
     * @covers \Magento\UrlRewrite\Controller\Router::match
     * @covers \Magento\UrlRewrite\Model\Storage\DbStorage::doFindOneByData
     *
     * @param string $request
     * @param string $redirect
     * @param int $expectedCode
     * @return void
     *
     * @dataProvider requestDataProvider
     */
    public function testMatchUrlRewrite(
        string $request,
        string $redirect,
        int $expectedCode = HttpResponse::STATUS_CODE_301
    ): void {
        $this->dispatch($request);
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertEquals($expectedCode, $code, 'Invalid response code');

        if ($expectedCode !== HttpResponse::STATUS_CODE_200) {
            $location = $response->getHeader('Location')->getFieldValue();
            $this->assertStringEndsWith(
                $redirect,
                $location,
                'Invalid location header'
            );
        }
    }

    /**
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
            'Use Case #7: Rewrite: page-similar --(301)--> page-a; '
            . 'Request: page-similar?param=1 --(301)--> page-a?param=1' => [
                'request' => '/page-similar?param=1',
                'redirect' => '/page-a?param=1',
            ],
            'Use Case #8: Rewrite: page-similar/ --(301)--> page-b; '
            . 'Request: page-similar/?param=1 --(301)--> page-b?param=1' => [
                'request' => '/page-similar/?param=1',
                'redirect' => '/page-b?param=1',
            ],
            'Use Case #9: Rewrite: page-similar-query-param --(301)--> page-d?param1=1;'
            . 'Request: page-similar-query-param --(301)--> page-d?param1=1' => [
                'request' => '/page-similar-query-param',
                'redirect' => '/page-d?param1=1',
            ],
            'Use Case #10: Rewrite: page-similar-query-param --(301)--> page-d?param1=1; '
            . 'Request: page-similar-query-param?param2=1 --(301)--> page-d?param1=1&param2=1' => [
                'request' => '/page-similar-query-param?param2=1',
                'redirect' => '/page-d?param1=1&param2=1',
            ],
            'Use Case #11: Rewrite: page-similar-query-param/ --(301)--> page-e?param1=1; '
            . 'Request: page-similar-query-param/ --(301)--> page-e?param1=1' => [
                'request' => '/page-similar-query-param/',
                'redirect' => '/page-e?param1=1',
            ],
            'Use Case #12: Rewrite: page-similar-query-param/ --(301)--> page-e?param1=1;'
            . 'Request: page-similar-query-param/?param2=1 --(301)--> page-e?param1=1&param2=1' => [
                'request' => '/page-similar-query-param/?param2=1',
                'redirect' => '/page-e?param1=1&param2=1',
            ],
        ];
    }
}
