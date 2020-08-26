<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Elasticsearch\Model\Indexer\IndexStructure;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexStructureTest extends TestCase
{
    /**
     * @var IndexStructure
     */
    private $model;

    /**
     * @var Elasticsearch|MockObject
     */
    private $adapter;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scopeInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getMockBuilder(Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockForAbstractClass(
            ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            ScopeInterface::class,
            [],
            '',
            false
        );

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            IndexStructure::class,
            [
                'adapter' => $this->adapter,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    public function testDelete()
    {
        $scopeId = 9;
        $dimension = $this->getMockBuilder(Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter->expects($this->any())
            ->method('cleanIndex');
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($scopeId);

        $this->model->delete('product', [$dimension]);
    }

    public function testCreate()
    {
        $scopeId = 9;
        $dimension = $this->getMockBuilder(Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter->expects($this->any())
            ->method('checkIndex');
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($scopeId);

        $this->model->create('product', [], [$dimension]);
    }
}
