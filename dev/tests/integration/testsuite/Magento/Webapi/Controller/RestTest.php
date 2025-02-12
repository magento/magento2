<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Controller;

use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RestTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Rest
     */
    private $controller;

    protected function setUp(): void
    {
        $this->request = Bootstrap::getObjectManager()->create(Request::class);
        $this->response = Bootstrap::getObjectManager()->create(Response::class);
        $this->controller = Bootstrap::getObjectManager()->create(
            Rest::class,
            [
                'request' => $this->request,
                'response' => $this->response,
            ]
        );
    }

    public function testDispatchUnsupportedMethod(): void
    {
        $this->request->setMethod('OPTIONS');
        $this->controller->dispatch($this->request);
        self::assertTrue($this->response->isException());
        /** @var WebapiException $exception */
        $exception = $this->response->getException()[0];
        self::assertInstanceOf(WebapiException::class, $exception);
        self::assertEquals(405, $exception->getHttpCode());
    }
}
