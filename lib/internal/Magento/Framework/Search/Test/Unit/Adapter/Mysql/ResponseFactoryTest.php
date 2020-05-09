<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Adapter\Mysql\DocumentFactory;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory
     */
    private $factory;

    /**
     * @var DocumentFactory|MockObject
     */
    private $documentFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->documentFactory = $this->getMockBuilder(DocumentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->factory = $helper->getObject(
            ResponseFactory::class,
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
            'total' => 2
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($rawResponse['documents'][0])
            ->willReturn('document1');
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($rawResponse['documents'][1])
            ->willReturn('document2');

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                QueryResponse::class,
                ['documents' => ['document1', 'document2'], 'aggregations' => null, 'total' => 2]
            )
            ->willReturn('QueryResponseObject');

        $result = $this->factory->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
