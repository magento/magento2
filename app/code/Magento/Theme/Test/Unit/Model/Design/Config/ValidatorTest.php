<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Model\Data\Design\Config\Data;
use Magento\Theme\Model\Design\Config\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Magento\Theme\Test\Unit\Model\Design\Config\Validator.
 */
class ValidatorTest extends TestCase
{
    /**
     * @var \Magento\Framework\Mail\TemplateInterfaceFactory|MockObject
     */
    private $templateFactory;

    /**
     * @var TemplateInterface|MockObject
     */
    private $template;

    /**
     * @var DesignConfigInterface|MockObject
     */
    private $designConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->templateFactory = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->template = $this->getMockBuilder(TemplateInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(
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
        $this->designConfig = $this->getMockBuilder(DesignConfigInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     * @throws LocalizedException
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
        $this->template->expects($this->once())->method('getTemplateText')->willReturn('');
        $this->template->expects($this->once())->method('revertDesign');

        $extensionAttributes = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDesignConfigData'])
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
     * @return Data
     */
    private function getDesignConfigData(array $data = []): Data
    {
        return $this->objectManager->getObject(
            Data::class,
            [
                'data' => $data,
            ]
        );
    }
}
