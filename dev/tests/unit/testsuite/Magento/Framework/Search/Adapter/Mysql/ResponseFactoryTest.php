<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\TestFramework\Helper\ObjectManager;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
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

        $this->documentFactory = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\DocumentFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->factory = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\ResponseFactory',
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
        $exceptedResponse = [
            'documents' => [
                [
                    ['name' => 'title', 'value' => 'oneTitle'],
                    ['name' => 'description', 'value' => 'oneDescription'],
                ],
                [
                    ['name' => 'title', 'value' => 'twoTitle'],
                    ['name' => 'description', 'value' => 'twoDescription'],
                ],
            ],
            'aggregations' => [],
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($exceptedResponse['documents'][0]))
            ->will($this->returnValue('document1'));
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($exceptedResponse['documents'][1])
            ->will($this->returnValue('document2'));

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo('Magento\Framework\Search\Response\QueryResponse'),
                $this->equalTo(['documents' => ['document1', 'document2'], 'aggregations' => null])
            )
            ->will($this->returnValue('QueryResponseObject'));

        $result = $this->factory->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
