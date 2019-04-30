<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Filter\RemoveFieldsFilter;
use Magento\AuthorizenetAcceptjs\Gateway\Http\TransferFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http\TransferFactory
 */
class TransferFactoryTest extends TestCase
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var TransferFactory|MockObject
     */
    private $transferMock;

    /**
     * @var TransferBuilder|MockObject
     */
    private $transferBuilder;

    /**
     * @var FilterInterface|MockObject
     */
    private $filterMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->transferBuilder = $this->createMock(TransferBuilder::class);
        $this->transferMock = $this->createMock(TransferInterface::class);
        $this->filterMock = $this->createMock(RemoveFieldsFilter::class);

        $this->transferFactory = $objectManagerHelper->getObject(
            TransferFactory::class,
            [
                'transferBuilder' => $this->transferBuilder,
                'payloadFilters' => [$this->filterMock],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $request = ['data1', 'data2'];

        // Assert the filter was created
        $this->filterMock->expects($this->once())
            ->method('filter')
            ->with($request)
            ->willReturn($request);

        // Assert the body of the transfer was set
        $this->transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $this->transferBuilder->method('build')
            ->willReturn($this->transferMock);

        $this->assertEquals($this->transferMock, $this->transferFactory->create($request));
    }
}
