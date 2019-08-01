<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Design\Config\Validator;

/**
 * Unit tests for Magento\Theme\Test\Unit\Model\Design\Config\Validator.
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mail\TemplateInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactory;

    /**
     * @var \Magento\Framework\Mail\TemplateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $template;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $designConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->templateFactory = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->template = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'emulateDesign',
                    'setForcedArea',
                    'loadDefault',
                    'getTemplateText',
                    'revertDesign',
                ]
            )
            ->getMockForAbstractClass();
        $this->templateFactory->expects($this->any())->method('create')->willReturn($this->template);
        $this->designConfig = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testGetDefaultTemplateTextDefaultScope(): void
    {
        $templateId = 'email_template';
        $designData = [
            'field_config' => ['field' => 'fieldValue'],
            'value' => $templateId,
        ];

        $this->templateFactory->expects($this->once())->method('create');
        $this->designConfig->expects($this->any())->method('getScope')->willReturn('default');
        $this->template->expects($this->once())->method('emulateDesign');
        $this->template->expects($this->once())->method('setForcedArea')->with($templateId);
        $this->template->expects($this->once())->method('loadDefault')->with($templateId);
        $this->template->expects($this->once())->method('getTemplateText');
        $this->template->expects($this->once())->method('revertDesign');

        $extensionAttributes = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDesignConfigData'])
            ->getMockForAbstractClass();

        $extensionAttributes->expects($this->any())->method('getDesignConfigData')->willReturn(
            [
                $this->getDesignConfigData($designData),
            ]
        );

        $this->designConfig->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        /** @var Validator $validator */
        $validator = $this->objectManager->getObject(
            Validator::class,
            [
                'templateFactory' => $this->templateFactory,
                'fields' => ['field' => 'fieldValue'],
            ]
        );
        $validator->validate($this->designConfig);
    }

    /**
     * Returns design config data object.
     *
     * @param array $data
     * @return \Magento\Theme\Model\Data\Design\Config\Data
     */
    private function getDesignConfigData(array $data = []): \Magento\Theme\Model\Data\Design\Config\Data
    {
        return $this->objectManager->getObject(
            \Magento\Theme\Model\Data\Design\Config\Data::class,
            [
                'data' => $data,
            ]
        );
    }
}
