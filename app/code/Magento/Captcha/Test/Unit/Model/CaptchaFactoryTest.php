<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model;

class CaptchaFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**@var \PHPUnit\Framework\MockObject\MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Captcha\Model\CaptchaFactory */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Captcha\Model\CaptchaFactory($this->_objectManagerMock);
    }

    public function testCreatePositive()
    {
        $captchaType = 'default';

        $defaultCaptchaMock = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType))
        )->willReturn(
            $defaultCaptchaMock
        );

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }

    public function testCreateNegative()
    {
        $captchaType = 'wrong_instance';

        $defaultCaptchaMock = $this->createMock(\stdClass::class);

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType))
        )->willReturn(
            $defaultCaptchaMock
        );

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Magento\Captcha\Model\\' . ucfirst($captchaType) .
            ' does not implement \Magento\Captcha\Model\CaptchaInterface');

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }
}
