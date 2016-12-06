<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config;

use Magento\Email\Model\Template;

/**
 * Class ValidatorTest to test \Magento\Theme\Model\Design\Config\Validator
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    const TEMPLATE_CODE = 'email_exception_fixture';

    /**
     * @var \Magento\Theme\Model\Design\Config\Validator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactoryMock;

    /**
     * @var \Magento\Email\Model\Template
     */
    private $templateModel;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\AreaList::class)
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
        $objectManager->get(\Magento\Framework\App\State::class)
            ->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $this->templateFactoryMock = $this->getMockBuilder(\Magento\Framework\Mail\TemplateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateModel = $objectManager->create(\Magento\Email\Model\Template::class);
        $this->templateModel->load(self::TEMPLATE_CODE, 'template_code');
        $this->templateFactoryMock->expects($this->once())
            ->method("create")
            ->willReturn($this->templateModel);
        $this->model = $objectManager->create(
            \Magento\Theme\Model\Design\Config\Validator::class,
            [ 'templateFactory' => $this->templateFactoryMock ]
        );
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email_header_template contains an incorrect configuration. The template has a
     */
    public function testValidateHasRecursiveReference()
    {
        if (!$this->templateModel->getId()) {
            $this->fail('Cannot load Template model');
        }

        $fieldConfig = [
            'path' => 'design/email/header_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_header_template'
        ];

        $designConfigMock = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designElementMock = $this->getMockBuilder(\Magento\Theme\Model\Data\Design\Config\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($designConfigExtensionMock);
        $designConfigExtensionMock->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$designElementMock]);
        $designElementMock->expects($this->any())->method('getFieldConfig')->willReturn($fieldConfig);
        $designElementMock->expects($this->once())->method('getPath')->willReturn($fieldConfig['path']);
        $designElementMock->expects($this->once())->method('getValue')->willReturn($this->templateModel->getId());

        $this->model->validate($designConfigMock);
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testValidateNoRecursiveReference()
    {
        $this->templateFactoryMock->expects($this->once())
            ->method("create")
            ->willReturn($this->templateModel);

        $fieldConfig = [
            'path' => 'design/email/footer_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_footer_template'
        ];

        $designConfigMock = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designConfigExtensionMock =
            $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designElementMock = $this->getMockBuilder(\Magento\Theme\Model\Data\Design\Config\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($designConfigExtensionMock);
        $designConfigExtensionMock->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$designElementMock]);
        $designElementMock->expects($this->any())->method('getFieldConfig')->willReturn($fieldConfig);
        $designElementMock->expects($this->once())->method('getPath')->willReturn($fieldConfig['path']);
        $designElementMock->expects($this->once())->method('getValue')->willReturn($this->templateModel->getId());

        $this->model->validate($designConfigMock);
    }
}
