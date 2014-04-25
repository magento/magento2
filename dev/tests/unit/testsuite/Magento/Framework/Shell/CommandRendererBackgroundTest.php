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
namespace Magento\Framework\Shell;

class CommandRendererBackgroundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test data for command
     *
     * @var string
     */
    protected $testCommand = 'php -r test.php';

    /**
     * @var \Magento\Framework\OsInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $osInfo;

    public function setUp()
    {
        $this->osInfo = $this->getMockBuilder('Magento\Framework\OsInfo')->getMock();
    }

    /**
     * @dataProvider commandPerOsTypeDataProvider
     * @param bool $isWindows
     * @param string $expectedResults
     */
    public function testRender($isWindows, $expectedResults)
    {
        $this->osInfo->expects($this->once())
            ->method('isWindows')
            ->will($this->returnValue($isWindows));

        $commandRenderer = new CommandRendererBackground($this->osInfo);
        $this->assertEquals(
            $expectedResults,
            $commandRenderer->render($this->testCommand)
        );
    }

    /**
     * Data provider for each os type
     *
     * @return array
     */
    public function commandPerOsTypeDataProvider()
    {
        return array(
            'windows' => array(true, 'start /B "magento background task" ' . $this->testCommand . ' 2>&1'),
            'unix'    => array(false, $this->testCommand . ' 2>&1 > /dev/null &'),
        );
    }
}
