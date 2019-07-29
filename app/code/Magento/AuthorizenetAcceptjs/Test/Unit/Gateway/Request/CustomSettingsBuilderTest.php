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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Request\CustomSettingsBuilder
 */
class CustomSettingsBuilderTest extends TestCase
{
    /**
     * @var CustomSettingsBuilder
     */
    private $builder;

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
        $this->configMock = $this->createMock(Config::class);
        /** @var MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);
        $this->subjectReaderMock->method('readStoreId')
            ->willReturn('123');

        $this->builder = new CustomSettingsBuilder($this->subjectReaderMock, $this->configMock);
    }

    /**
     * @return void
     */
    public function testBuildWithEmailCustomerDisabled()
    {
        $this->configMock->method('shouldEmailCustomer')
            ->with('123')
            ->willReturn(false);

        $this->assertEquals([], $this->builder->build([]));
    }

    /**
     * @return void
     */
    public function testBuildWithEmailCustomerEnabled()
    {
        $this->configMock->method('shouldEmailCustomer')
            ->with('123')
            ->willReturn(true);

        $expected = [
            'transactionRequest' => [
                'transactionSettings' => [
                    'setting' => [
                        [
                            'settingName' => 'emailCustomer',
                            'settingValue' => 'true',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->builder->build([]));
    }
}
