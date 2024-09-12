<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Model\Data\Design\Config\Data;
use Magento\Theme\Model\Design\Config\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidatorTest to test \Magento\Theme\Model\Design\Config\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $model;

    /**
     * @var \Magento\Framework\Mail\TemplateInterfaceFactory
     */
    private $templateFactoryMock;

    protected function setUp(): void
    {
        $this->templateFactoryMock = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Validator::class,
            [
                "templateFactory" => $this->templateFactoryMock,
                "fields" => ["email_header_template", "no_reference"]
            ]
        );
    }

    public function testValidateHasRecursiveReference()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $fieldConfig = [
            'path' => 'design/email/header_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_header_template'
        ];

        $designConfigMock = $this->getMockBuilder(DesignConfigInterface::class)
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
                ->addMethods(['getDesignConfigData'])
                ->getMockForAbstractClass();
        $designElementMock = $this->getMockBuilder(Data::class)
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

        $templateMock = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterface::class)
            ->addMethods(['getTemplateText', 'emulateDesign', 'loadDefault', 'revertDesign', 'setForcedArea'])
            ->getMockForAbstractClass();

        $this->templateFactoryMock->expects($this->once())->method('create')->willReturn($templateMock);
        $templateMock->expects($this->once())->method('getTemplateText')->willReturn(
            file_get_contents(__DIR__ . '/_files/template_fixture.html')
        );

        $this->model->validate($designConfigMock);

        $this->expectExceptionMessage(
            'The "email_header_template" template contains an incorrect configuration, with a reference to itself. '
            . 'Remove or change the reference, then try again.'
        );
    }

    public function testValidateNoRecursiveReference()
    {
        $fieldConfig = [
            'path' => 'no/reference',
            'fieldset' => 'no/reference',
            'field' => 'no_reference'
        ];

        $designConfigMock = $this->getMockBuilder(DesignConfigInterface::class)
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
                ->addMethods(['getDesignConfigData'])
                ->getMockForAbstractClass();
        $designElementMock = $this->getMockBuilder(Data::class)
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

        $templateMock = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterface::class)
            ->addMethods(['getTemplateText', 'emulateDesign', 'loadDefault', 'revertDesign', 'setForcedArea'])
            ->getMockForAbstractClass();

        $this->templateFactoryMock->expects($this->once())->method('create')->willReturn($templateMock);
        $templateMock->expects($this->once())->method('getTemplateText')->willReturn(
            file_get_contents(__DIR__ . '/_files/template_fixture.html')
        );

        $this->model->validate($designConfigMock);
    }
}
