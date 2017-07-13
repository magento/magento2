<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    private $factory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->documentFactory = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\DocumentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->factory = $helper->getObject(
            \Magento\Framework\Search\Adapter\Mysql\ResponseFactory::class,
            ['documentFactory' => $this->documentFactory, 'objectManager' => $this->objectManager]
        );
    }

    public function testCreate()
    {
        $rawResponse = [
            'documents' => [
                ['title' => 'oneTitle', 'description' => 'oneDescription'],
                ['title' => 'twoTitle', 'description' => 'twoDescription'],
            ],
            'aggregations' => [],
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($rawResponse['documents'][0]))
            ->will($this->returnValue('document1'));
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($rawResponse['documents'][1])
            ->will($this->returnValue('document2'));

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Response\QueryResponse::class),
                $this->equalTo(['documents' => ['document1', 'document2'], 'aggregations' => null])
            )
            ->will($this->returnValue('QueryResponseObject'));

        $result = $this->factory->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
