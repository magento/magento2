<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $inputParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
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
        $inputParams = ['param' => 'value', 'namespace' => 'Magento', 'module' => 'Core'];
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
        return [
            'no namespace' => [['module' => 'Core']],
            'no module' => [['namespace' => 'Magento']]
        ];
    }
}
