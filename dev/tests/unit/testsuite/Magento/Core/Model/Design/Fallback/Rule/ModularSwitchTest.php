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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Design\Fallback\Rule;

class ModularSwitchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Design\Fallback\Rule\ModularSwitch
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleNonModular;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleModular;

    protected function setUp()
    {
        $this->_ruleNonModular = $this->getMockForAbstractClass(
            'Magento\Core\Model\Design\Fallback\Rule\RuleInterface'
        );
        $this->_ruleModular = $this->getMockForAbstractClass(
            'Magento\Core\Model\Design\Fallback\Rule\RuleInterface'
        );
        $this->_object = new \Magento\Core\Model\Design\Fallback\Rule\ModularSwitch(
            $this->_ruleNonModular, $this->_ruleModular
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_ruleNonModular = null;
        $this->_ruleModular = null;
    }

    public function testGetPatternDirsNonModular()
    {
        $inputParams = array('param_one' => 'value_one', 'param_two' => 'value_two');
        $expectedResult = new \stdClass();
        $this->_ruleNonModular
            ->expects($this->once())
            ->method('getPatternDirs')
            ->with($inputParams)
            ->will($this->returnValue($expectedResult))
        ;
        $this->_ruleModular
            ->expects($this->never())
            ->method('getPatternDirs')
        ;
        $this->assertSame($expectedResult, $this->_object->getPatternDirs($inputParams));
    }

    public function testGetPatternDirsModular()
    {
        $inputParams = array('param' => 'value', 'namespace' => 'Magento', 'module' => 'Core');
        $expectedResult = new \stdClass();
        $this->_ruleNonModular
            ->expects($this->never())
            ->method('getPatternDirs')
        ;
        $this->_ruleModular
            ->expects($this->once())
            ->method('getPatternDirs')
            ->with($inputParams)
            ->will($this->returnValue($expectedResult))
        ;
        $this->assertSame($expectedResult, $this->_object->getPatternDirs($inputParams));
    }

    /**
     * @param array $inputParams
     * @dataProvider getPatternDirsExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameters 'namespace' and 'module' should either be both set or unset
     */
    public function testGetPatternDirsException(array $inputParams)
    {
        $this->_object->getPatternDirs($inputParams);
    }

    public function getPatternDirsExceptionDataProvider()
    {
        return array(
            'no namespace'  => array(array('module' => 'Core')),
            'no module'     => array(array('namespace' => 'Magento')),
        );
    }
}
