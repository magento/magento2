<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP;

use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing asynchronous HTTP client.
 */
class AsyncClientInterfaceTest extends TestCase
{
    /**
     * @var AsyncClientInterface
     */
    private $client;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->client = Bootstrap::getObjectManager()->get(AsyncClientInterface::class);
    }

    /**
     * Making a request.
     */
    public function testRequest(): void
    {
        $request = new Request('https://magento.com', Request::METHOD_GET, [], null);
        $response1 = $this->client->request($request);
        $response2 = $this->client->request($request);
        $this->assertEquals(200, $response2->get()->getStatusCode());
        $this->assertEquals(200, $response1->get()->getStatusCode());
        $this->assertStringContainsString('Magento Commerce', $response1->get()->getBody());
        $this->assertStringContainsString('Magento Commerce', $response2->get()->getBody());
        $date1 = new \DateTime($response1->get()->getHeaders()['date']);
        $date2 = new \DateTime($response2->get()->getHeaders()['date']);
        $this->assertLessThanOrEqual(1, abs((int)$date1->format('U') - (int)$date2->format('U')));
    }

    /**
     * Test cancelling a request.
     *
     */
    public function testCancel(): void
    {
        $this->expectException(\Magento\Framework\Async\CancelingDeferredException::class);
        $this->expectExceptionMessage('Deferred is canceled');

        $request = new Request('https://magento.com/home-page', Request::METHOD_GET, [], null);
        $response = $this->client->request($request);
        $response->cancel(true);
        $this->assertTrue($response->isCancelled());
        $response->get();
    }

    /**
     * Test failing cancelling a request.
     *
     */
    public function testCancelFail(): void
    {
        $this->expectException(\Magento\Framework\Async\CancelingDeferredException::class);
        $this->expectExceptionMessage('Failed to cancel HTTP request');

        $request = new Request('https://magento.com/home-page', Request::METHOD_GET, [], null);
        $response = $this->client->request($request);
        $response->cancel();
    }
}
