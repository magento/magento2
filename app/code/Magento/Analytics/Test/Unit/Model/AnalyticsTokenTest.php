<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AnalyticsTokenTest
 */
class AnalyticsTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
