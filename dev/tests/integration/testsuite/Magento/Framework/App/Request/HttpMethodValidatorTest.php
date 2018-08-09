<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;

class HttpMethodValidatorTest extends TestCase
{
    /**
     * @var HttpMethodValidator
     */
    private $validator;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var HttpMethodMap
     */
    private $map;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->validator = $objectManager->get(HttpMethodValidator::class);
        $this->request = $objectManager->get(RequestInterface::class);
        if (!$this->request instanceof HttpRequest) {
            throw new \RuntimeException('We need HTTP request');
        }
        $this->map = $objectManager->get(HttpMethodMap::class);
    }

    /**
     * @return array
     */
    private function getMap(): array
    {
        $map = $this->map->getMap();
        if (count($map) < 2) {
            throw new \RuntimeException(
                'We need at least 2 HTTP methods allowed'
            );
        }

        $sorted = [];
        foreach ($map as $method => $interface) {
            $sorted[] = ['method' => $method, 'interface' => $interface];
        }

        return $sorted;
    }

    /**
     * Test positive case.
     *
     * @throws InvalidRequestException
     */
    public function testAllowed()
    {
        $map = $this->getMap();

        $action1 = $this->getMockForAbstractClass($map[0]['interface']);
        $this->request->setMethod($map[0]['method']);
        $this->validator->validate($this->request, $action1);

        $action2 = $this->getMockForAbstractClass(ActionInterface::class);
        $this->validator->validate($this->request, $action2);
    }

    /**
     * Test negative case.
     *
     * @expectedException \Magento\Framework\App\Request\InvalidRequestException
     */
    public function testNotAllowedMethod()
    {
        $this->request->setMethod('method' .rand(0, 1000));
        $action = $this->getMockForAbstractClass(ActionInterface::class);

        $this->validator->validate($this->request, $action);
    }

    /**
     * @expectedException \Magento\Framework\App\Request\InvalidRequestException
     */
    public function testRestrictedMethod()
    {
        $map = $this->getMap();

        $this->request->setMethod($map[1]['method']);
        $action = $this->getMockForAbstractClass($map[0]['interface']);

        $this->validator->validate($this->request, $action);
    }
}
