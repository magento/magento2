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

class SolutionDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SolutionDataBuilder
     */
    private $builder;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

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
