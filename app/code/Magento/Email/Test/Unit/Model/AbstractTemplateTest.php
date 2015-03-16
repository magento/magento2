<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Email\Model\AbstractTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\AbstractTemplate;

class AbstractTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Template mock
     *
     * @var AbstractTemplate
     */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Email\Model\AbstractTemplate',
            $helper->getConstructArguments(
                'Magento\Email\Model\AbstractTemplate',
                [
                    'design' => $this->getMock('Magento\Framework\View\DesignInterface'),
                    'data' => ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1]
                ]
            )
        );
    }

    /**
     * @param array $config
     * @expectedException \Magento\Framework\Exception
     * @dataProvider invalidInputParametersDataProvider
     */
    public function testSetDesignConfigWithInvalidInputParametersThrowsException($config)
    {
        $this->_model->setDesignConfig($config);
    }

    public function testSetDesignConfigWithValidInputParametersReturnsSuccess()
    {
        $config = ['area' => 'some_area', 'store' => 1];
        $this->_model->setDesignConfig($config);
        $this->assertEquals($config, $this->_model->getDesignConfig()->getData());
    }

    public function invalidInputParametersDataProvider()
    {
        return [[[]], [['area' => 'some_area']], [['store' => 'any_store']]];
    }

    public function testEmulateDesignAndRevertDesign()
    {
        $originalConfig = ['area' => 'some_area', 'store' => 1];
        $expectedConfig = ['area' => 'frontend', 'store' => 2];
        $this->_model->setDesignConfig($originalConfig);

        $this->_model->emulateDesign(2);
        // assert config data has been emulated
        $this->assertEquals($expectedConfig, $this->_model->getDesignConfig()->getData());

        $this->_model->revertDesign();
        // assert config data has been reverted to the original state
        $this->assertEquals($originalConfig, $this->_model->getDesignConfig()->getData());
    }

    public function testGetDesignConfig()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $designMock = $this->getMock('Magento\Framework\View\DesignInterface');
        $designMock->expects($this->any())->method('getArea')->willReturn('test_area');

        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getId')->willReturn(2);
        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $model = $this->getMockForAbstractClass(
            'Magento\Email\Model\AbstractTemplate',
            $helper->getConstructArguments(
                'Magento\Email\Model\AbstractTemplate',
                [
                    'design' => $designMock,
                    'storeManager' => $storeManagerMock
                ]
            )
        );

        $expectedConfig = ['area' => 'test_area', 'store' => 2];
        $this->assertEquals($expectedConfig, $model->getDesignConfig()->getData());
    }
}
