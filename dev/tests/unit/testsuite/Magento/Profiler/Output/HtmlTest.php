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
 * @category    Magento
 * @package     Magento_Profiler
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Profiler_Output_Html
 *
 * @group profiler
 */
class Magento_Profiler_Output_HtmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Profiler_Output_Html|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    public static function setUpBeforeClass()
    {
        Magento_Profiler::enable();
        /* Profiler measurements fixture */
        $timersProperty = new ReflectionProperty('Magento_Profiler', '_timers');
        $timersProperty->setAccessible(true);
        $timersProperty->setValue(include __DIR__ . '/../_files/timers.php');
        $timersProperty->setAccessible(false);
    }

    public static function tearDownAfterClass()
    {
        Magento_Profiler::reset();
    }

    protected function setUp()
    {
        $this->_object = $this->getMock('Magento_Profiler_Output_Html', array('_renderCaption'));
        $this->_object
            ->expects($this->any())
            ->method('_renderCaption')
            ->will($this->returnValue('Code Profiler Title'))
        ;
    }

    public function testDisplay()
    {
        ob_start();
        $this->_object->display();
        $actualHtml = ob_get_clean();
        $expectedString = file_get_contents(__DIR__ . '/../_files/output.html');
        $expectedString = ltrim(preg_replace('/^<!--.+?-->/s', '', $expectedString));
        $this->assertEquals($expectedString, $actualHtml);
    }
}
