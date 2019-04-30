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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request\SolutionDataBuilder
 */
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        /** @var MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);

        $this->builder = $objectManagerHelper->getObject(
            SolutionDataBuilder::class,
            [
                'config' => $this->configMock,
                'subjectReader' => $this->subjectReaderMock,
            ]
        );
    }

    /**
     * @return void
     */
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
                ],
            ],
        ];

        $buildSubject = [];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
