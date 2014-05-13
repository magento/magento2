<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\DesignEditor\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test front name prefix
     */
    const TEST_FRONT_NAME = 'test_front_name';

    /**
     * @var array
     */
    protected $_disabledCacheTypes = array('type1', 'type2');

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    protected function setUp()
    {
        $this->_context = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_context);
    }

    public function testGetFrontName()
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data($this->_context, self::TEST_FRONT_NAME);
        $this->assertEquals(self::TEST_FRONT_NAME, $this->_model->getFrontName());
    }

    /**
     * @param string $path
     * @param bool $expected
     * @dataProvider isVdeRequestDataProvider
     */
    public function testIsVdeRequest($path, $expected)
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data($this->_context, self::TEST_FRONT_NAME);
        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $requestMock->expects($this->once())->method('getOriginalPathInfo')->will($this->returnValue($path));
        $this->assertEquals($expected, $this->_model->isVdeRequest($requestMock));
    }

    /**
     * @return array
     */
    public function isVdeRequestDataProvider()
    {
        $vdePath = self::TEST_FRONT_NAME . '/' . \Magento\DesignEditor\Model\State::MODE_NAVIGATION . '/';
        return array(
            array($vdePath . '1/category.html', true),
            array('/1/category.html', false),
            array('/1/2/3/4/5/6/7/category.html', false)
        );
    }

    public function testGetDisabledCacheTypes()
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data(
            $this->_context,
            self::TEST_FRONT_NAME,
            array('type1', 'type2')
        );
        $this->assertEquals($this->_disabledCacheTypes, $this->_model->getDisabledCacheTypes());
    }
}
