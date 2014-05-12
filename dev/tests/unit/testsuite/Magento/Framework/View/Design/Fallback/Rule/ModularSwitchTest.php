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
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * ModularSwitch Test
 *
 */
class ModularSwitchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModularSwitch
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RuleInterface
     */
    protected $ruleNonModular;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RuleInterface
     */
    protected $ruleModular;

    protected function setUp()
    {
        $this->ruleNonModular = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Design\Fallback\Rule\RuleInterface'
        );
        $this->ruleModular = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Design\Fallback\Rule\RuleInterface'
        );
        $this->object = new ModularSwitch($this->ruleNonModular, $this->ruleModular);
    }

    protected function tearDown()
    {
        $this->object = null;
        $this->ruleNonModular = null;
        $this->ruleModular = null;
    }

    public function testGetPatternDirsNonModular()
    {
        $inputParams = array('param_one' => 'value_one', 'param_two' => 'value_two');
        $expectedResult = new \stdClass();
        $this->ruleNonModular->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->will(
            $this->returnValue($expectedResult)
        );

        $this->ruleModular->expects($this->never())->method('getPatternDirs');

        $this->assertSame($expectedResult, $this->object->getPatternDirs($inputParams));
    }

    public function testGetPatternDirsModular()
    {
        $inputParams = array('param' => 'value', 'namespace' => 'Magento', 'module' => 'Core');
        $expectedResult = new \stdClass();
        $this->ruleNonModular->expects($this->never())->method('getPatternDirs');

        $this->ruleModular->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->will(
            $this->returnValue($expectedResult)
        );

        $this->assertSame($expectedResult, $this->object->getPatternDirs($inputParams));
    }

    /**
     * @param array $inputParams
     * @dataProvider getPatternDirsExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameters 'namespace' and 'module' should either be both set or unset
     */
    public function testGetPatternDirsException(array $inputParams)
    {
        $this->object->getPatternDirs($inputParams);
    }

    /**
     * @return array
     */
    public function getPatternDirsExceptionDataProvider()
    {
        return array(
            'no namespace' => array(array('module' => 'Core')),
            'no module' => array(array('namespace' => 'Magento'))
        );
    }
}
