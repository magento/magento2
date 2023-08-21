<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Config\DisableSession;
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
     * @inheirtDoc
     */
    public function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->model = (new ObjectManager($this))->getObject(
            DisableSession::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @dataProvider disableSessionDataProvider
     */
    public function testisSessionDisabled($configValue, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($configValue);
        $this->assertEquals($expectedResult, $this->model->isDisabled());
    }

    /**
     * Data provider for session disabled config test.
     * @return array[]
     */
    public function disableSessionDataProvider()
    {
        return [
            ['configValue' => '1', true],
            ['configValue' => '0', false],
            ['configValue' => '11', false],
            ['configValue' => null, false],
            ['configValue' => '', false],
            ['configValue' => 'adfjsadf', false],
        ];
    }
}
