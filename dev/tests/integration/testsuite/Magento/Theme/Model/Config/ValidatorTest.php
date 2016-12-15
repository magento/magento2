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
    /**
     * @var \Magento\Theme\Model\Design\Config\Validator
     */
    private $model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\AreaList')
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
        $objectManager->get('Magento\Framework\App\State')
            ->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $this->model = $objectManager->get('Magento\Theme\Model\Design\Config\Validator');
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email_header_template contains an incorrect configuration. The template has a
     */
    public function testValidateHasRecursiveReference()
    {
        $this->markTestSkipped("MAGETWO-59802:BuildFailureforL2onbranch2.1-develop");
        $fieldConfig = [
            'path' => 'design/email/header_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_header_template'
        ];

        $designConfigMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designConfigExtensionMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designElementMock = $this->getMockBuilder('Magento\Theme\Model\Data\Design\Config\Data')
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
        $designElementMock->expects($this->once())->method('getValue')->willReturn(1);

        $this->model->validate($designConfigMock);
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testValidateNoRecursiveReference()
    {
        $fieldConfig = [
            'path' => 'design/email/footer_template',
            'fieldset' => 'other_settings/email',
            'field' => 'email_footer_template'
        ];

        $designConfigMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designConfigExtensionMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $designElementMock = $this->getMockBuilder('Magento\Theme\Model\Data\Design\Config\Data')
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
        $designElementMock->expects($this->once())->method('getValue')->willReturn(1);

        $this->model->validate($designConfigMock);
    }
}
