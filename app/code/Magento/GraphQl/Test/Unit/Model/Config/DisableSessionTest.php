<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Config\DisableSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for DisableSession config model.
 */
class DisableSessionTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var DisableSession
     */
    private $model;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = (new ObjectManager($this))->getObject(
            DisableSession::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    #[DataProvider('disableSessionDataProvider')]
    public function testisSessionDisabled($configValue, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($configValue);
        $this->assertEquals($expectedResult, $this->model->isDisabled());
    }

    /**
     * Data provider for session disabled config test.
     * @return array[]
     */
    public static function disableSessionDataProvider()
    {
        return [
            ['1', true],
            ['0', false],
            ['11', false],
            [null, false],
            ['', false],
            ['adfjsadf', false],
        ];
    }
}
