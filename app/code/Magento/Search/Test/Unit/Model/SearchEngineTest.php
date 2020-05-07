<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\AdapterFactory;
use Magento\Search\Model\SearchEngine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchEngineTest extends TestCase
{
    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $adapterFactory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapterFactory->expects($this->once())->method('create')->willReturn($this->adapter);

        $this->searchEngine = $helper->getObject(
            SearchEngine::class,
            [
                'adapterFactory' => $adapterFactory,
            ]
        );
    }

    public function testSearch()
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->adapter->expects($this->once())
            ->method('query')
            ->with($request)
            ->willReturn($response);

        $result = $this->searchEngine->search($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
