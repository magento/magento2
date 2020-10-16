<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnalyticsTokenTest extends TestCase
{
    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var AnalyticsToken
     */
    private $tokenModel;

    /**
     * @var string
     */
    private $tokenPath = 'analytics/general/token';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->configWriterMock = $this->getMockForAbstractClass(WriterInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->tokenModel = $this->objectManagerHelper->getObject(
            AnalyticsToken::class,
            [
                'reinitableConfig' => $this->reinitableConfigMock,
                'config' => $this->configMock,
                'configWriter' => $this->configWriterMock,
                'tokenPath' => $this->tokenPath,
            ]
        );
    }

    /**
     * @return void
     */
    public function testStoreToken()
    {
        $value = 'jjjj0000';

        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with($this->tokenPath, $value);

        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->willReturnSelf();

        $this->assertTrue($this->tokenModel->storeToken($value));
    }

    /**
     * @return void
     */
    public function testGetToken()
    {
        $value = 'jjjj0000';

        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with($this->tokenPath)
            ->willReturn($value);

        $this->assertSame($value, $this->tokenModel->getToken());
    }

    /**
     * @return void
     */
    public function testIsTokenExist()
    {
        $this->assertFalse($this->tokenModel->isTokenExist());

        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with($this->tokenPath)
            ->willReturn('0000');
        $this->assertTrue($this->tokenModel->isTokenExist());
    }
}
