<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Helper;

use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\User\Helper\Data testing
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $model;

    /**
     * @var Random|MockObject
     */
    protected $mathRandomMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Data::class,
            [
                'config' => $this->configMock,
                'mathRandom' => $this->mathRandomMock
            ]
        );
    }

    public function testGenerateResetPasswordLinkToken()
    {
        $hash = 'hashString';
        $this->mathRandomMock->expects($this->once())->method('getUniqueHash')->willReturn($hash);
        $this->assertEquals($hash, $this->model->generateResetPasswordLinkToken());
    }

    public function testGetResetPasswordLinkExpirationPeriod()
    {
        $value = '123';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Data::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD)
            ->willReturn($value);
        $this->assertEquals((int) $value, $this->model->getResetPasswordLinkExpirationPeriod());
    }
}
