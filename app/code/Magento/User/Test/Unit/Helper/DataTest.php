<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Helper;

/**
 * Test class for \Magento\User\Helper\Data testing
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Helper\Data
     */
    protected $model;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    /**
     * @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->mathRandomMock = $this->getMockBuilder('Magento\Framework\Math\Random')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configMock = $this->getMockBuilder('Magento\Backend\App\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\User\Helper\Data',
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
            ->with(\Magento\User\Helper\Data::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD)
            ->willReturn($value);
        $this->assertEquals((int) $value, $this->model->getResetPasswordLinkExpirationPeriod());
    }
}
