<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Indexer\IndexStructure;

class IndexStructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexStructure
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Elasticsearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeInterface::class,
            [],
            '',
            false
        );

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Indexer\IndexStructure::class,
            [
                'adapter' => $this->adapter,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    public function testDelete()
    {
        $scopeId = 9;
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
