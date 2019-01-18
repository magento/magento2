<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Request\CustomSettingsBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;

class CustomSettingsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomSettingsBuilder
     */
    private $builder;

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
        $this->configMock = $this->createMock(Config::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);
        $this->subjectReaderMock->method('readStoreId')
            ->willReturn('123');

        $this->builder = new CustomSettingsBuilder($this->subjectReaderMock, $this->configMock);
    }

    public function testBuildWithEmailCustomerDisabled()
    {
        $this->configMock->expects($this->once())
            ->method('shouldEmailCustomer')
            ->with('123')
            ->willReturn(false);

        $this->assertEquals([], $this->builder->build([]));
    }

    public function testBuildWithEmailCustomerEnabled()
    {
        $this->configMock->expects($this->once())
            ->method('shouldEmailCustomer')
            ->with('123')
            ->willReturn(true);

        $expected = [
            'transactionRequest' => [
                'transactionSettings' => [
                    'setting' => [
                        [
                            'settingName' => 'emailCustomer',
                            'settingValue' => 'true'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->builder->build([]));
    }
}
