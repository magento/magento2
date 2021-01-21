<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Request\SolutionDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SolutionDataBuilderTest extends TestCase
{
    /**
     * @var SolutionDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        /** @var MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);

        $this->builder = new SolutionDataBuilder($this->subjectReaderMock, $this->configMock);
    }

    public function testBuild()
    {
        $this->subjectReaderMock->method('readStoreId')
            ->willReturn('123');
        $this->configMock->method('getSolutionId')
            ->with('123')
            ->willReturn('solutionid');

        $expected = [
            'transactionRequest' => [
                'solution' => [
                    'id' => 'solutionid',
                ]
            ]
        ];

        $buildSubject = [];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
