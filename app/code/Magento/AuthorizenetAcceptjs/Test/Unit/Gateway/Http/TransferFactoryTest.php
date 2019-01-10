<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AuthorizenetAcceptJs\Test\Unit\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Filter\RemoveFieldsFilter;
use Magento\AuthorizenetAcceptJs\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;

class TransferFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var TransferFactory
     */
    private $transferMock;

    /**
     * @var TransferBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferBuilder;

    /**
     * @var FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    protected function setUp()
    {
        $this->transferBuilder = $this->createMock(TransferBuilder::class);
        $this->transferMock = $this->createMock(TransferInterface::class);
        $this->filterMock = $this->createMock(RemoveFieldsFilter::class);

        $this->transferFactory = new TransferFactory(
            $this->transferBuilder,
            [$this->filterMock]
        );
    }

    public function testCreate()
    {
        $request = ['data1', 'data2'];

        $this->filterMock->expects($this->once())
            ->method('filter')
            ->with($request)
            ->willReturn($request);

        $this->transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('build')
            ->willReturn($this->transferMock);

        $this->assertEquals($this->transferMock, $this->transferFactory->create($request));
    }
}
