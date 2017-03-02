<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Config;

/**
 * Class ValidatorTest to test \Magento\Theme\Model\Design\Config\Validator
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Config\Validator
     */
    private $model;

    /**
     * @var \Magento\Framework\Mail\TemplateInterfaceFactory
     */
    private $templateFactoryMock;

    protected function setUp()
    {
        $this->templateFactoryMock = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Theme\Model\Design\Config\Validator::class,
            [
                "templateFactory" => $this->templateFactoryMock,
                "fields" => ["email_header_template", "no_reference"]
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email_header_template contains an incorrect configuration. The template has a
     */
    public function testValidateHasRecursiveReference()
    {
        $fieldConfig = [
            'path' => 'design/email/header_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_header_template'
        ];

        $designConfigMock = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
                ->setMethods(['getDesignConfigData'])
                ->getMock();
        $designElementMock = $this->getMockBuilder(\Magento\Theme\Model\Data\Design\Config\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($designConfigExtensionMock);
        $designConfigExtensionMock->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$designElementMock]);
        $designElementMock->expects($this->any())->method('getFieldConfig')->willReturn($fieldConfig);
        $designElementMock->expects($this->once())->method('getPath')->willReturn($fieldConfig['path']);
        $designElementMock->expects($this->once())->method('getValue')->willReturn($fieldConfig['field']);

        $templateMock = $this->getMockBuilder(\Magento\Email\Model\TemplateInterface::class)
            ->setMethods(['getTemplateText', 'emulateDesign', 'loadDefault', 'revertDesign'])
            ->getMock();

        $this->templateFactoryMock->expects($this->once())->method('create')->willReturn($templateMock);
        $templateMock->expects($this->once())->method('getTemplateText')->willReturn(
            file_get_contents(__DIR__ . '/_files/template_fixture.html')
        );

        $this->model->validate($designConfigMock);
    }

    public function testValidateNoRecursiveReference()
    {
        $fieldConfig = [
            'path' => 'no/reference',
            'fieldset' => 'no/reference',
            'field' => 'no_reference'
        ];

        $designConfigMock = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
                ->setMethods(['getDesignConfigData'])
                ->getMock();
        $designElementMock = $this->getMockBuilder(\Magento\Theme\Model\Data\Design\Config\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($designConfigExtensionMock);
        $designConfigExtensionMock->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$designElementMock]);
        $designElementMock->expects($this->any())->method('getFieldConfig')->willReturn($fieldConfig);
        $designElementMock->expects($this->once())->method('getPath')->willReturn($fieldConfig['path']);
        $designElementMock->expects($this->once())->method('getValue')->willReturn($fieldConfig['field']);

        $templateMock = $this->getMockBuilder(\Magento\Email\Model\TemplateInterface::class)
            ->setMethods(['getTemplateText', 'emulateDesign', 'loadDefault', 'revertDesign'])
            ->getMock();

        $this->templateFactoryMock->expects($this->once())->method('create')->willReturn($templateMock);
        $templateMock->expects($this->once())->method('getTemplateText')->willReturn(
            file_get_contents(__DIR__ . '/_files/template_fixture.html')
        );

        $this->model->validate($designConfigMock);
    }
}
