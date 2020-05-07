<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Http;

use Magento\Braintree\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransferFactoryTest extends TestCase
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
     * @var TransferBuilder|MockObject
     */
    private $transferBuilder;

    protected function setUp(): void
    {
        $this->transferBuilder = $this->createMock(TransferBuilder::class);
        $this->transferMock = $this->getMockForAbstractClass(TransferInterface::class);

        $this->transferFactory = new TransferFactory(
            $this->transferBuilder
        );
    }

    public function testCreate()
    {
        $request = ['data1', 'data2'];

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
