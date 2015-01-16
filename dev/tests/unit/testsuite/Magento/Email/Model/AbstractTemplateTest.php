<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Email\Model\AbstractTemplate.
 */
namespace Magento\Email\Model;

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
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
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
}
