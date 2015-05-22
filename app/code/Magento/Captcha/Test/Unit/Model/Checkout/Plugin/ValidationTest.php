<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model\Checkout\Plugin;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataExtMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Captcha\Model\Checkout\Plugin\Validation
     */
    protected $model;

    protected function setUp()
    {
        $formIds = [1,2,3];

        $this->captchaHelperMock = $this->getMock('Magento\Captcha\Helper\Data', [], [], '', false);
        $this->addressDataMock = $this->getMock('Magento\Quote\Api\Data\AddressAdditionalDataInterface');
        $this->addressDataExtMock = $this->getMock('Magento\Quote\Api\Data\AddressAdditionalDataExtensionInterface');
        $this->captchaMock = $this->getMock('Magento\Captcha\Model\DefaultModel', [], [], '', false);
        $this->processorMock = $this->getMock('Magento\Quote\Model\AddressAdditionalDataProcessor', [], [], '', false);

        $this->addressDataMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue($this->addressDataExtMock));

        $this->model = new \Magento\Captcha\Model\Checkout\Plugin\Validation($this->captchaHelperMock, $formIds);
    }

    public function testBeforeProcessCaptchaIsCorrect()
    {
        $formId = 1;
        $text = 'text';
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaFormId')->will($this->returnValue($formId));
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaString')->will($this->returnValue($text));
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with($formId)
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->any())->method('isRequired')->will($this->returnValue(true));
        $this->captchaMock->expects($this->once())->method('isCorrect')->with($text)->will($this->returnValue(true));

        $this->model->beforeProcess($this->processorMock, $this->addressDataMock);
    }

    public function testBeforeProcessCaptchaIsNotRequired()
    {
        $formId = 1;
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaFormId')->will($this->returnValue($formId));
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaString')->will($this->returnValue('text'));
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with($formId)
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->captchaMock->expects($this->never())->method('isCorrect');

        $this->model->beforeProcess($this->processorMock, $this->addressDataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Provided form does not exist
     */
    public function testBeforeProcessUnknownForm()
    {
        $formId = 4;
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaFormId')->will($this->returnValue($formId));
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaString')->will($this->returnValue('text'));

        $this->model->beforeProcess($this->processorMock, $this->addressDataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Incorrect CAPTCHA
     */
    public function testBeforeProcessIncorrectCaptcha()
    {
        $formId = 1;
        $text = 'text';
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaFormId')->will($this->returnValue($formId));
        $this->addressDataExtMock->expects($this->any())->method('getCaptchaString')->will($this->returnValue($text));
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with($formId)
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->any())->method('isRequired')->will($this->returnValue(true));
        $this->captchaMock->expects($this->once())->method('isCorrect')->with($text)->will($this->returnValue(false));

        $this->model->beforeProcess($this->processorMock, $this->addressDataMock);
    }
}
