<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model;

use Magento\Captcha\Model\CaptchaFactory;
use Magento\Captcha\Model\DefaultModel;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class CaptchaFactoryTest extends TestCase
{
    /**@var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var CaptchaFactory */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->_model = new CaptchaFactory($this->_objectManagerMock);
    }

    public function testCreatePositive()
    {
        $captchaType = 'default';

        $defaultCaptchaMock = $this->createMock(DefaultModel::class);

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType))
        )->will(
            $this->returnValue($defaultCaptchaMock)
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
        )->will(
            $this->returnValue($defaultCaptchaMock)
        );

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Magento\Captcha\Model\\' . ucfirst($captchaType) .
            ' does not implement \Magento\Captcha\Model\CaptchaInterface');

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }
}
