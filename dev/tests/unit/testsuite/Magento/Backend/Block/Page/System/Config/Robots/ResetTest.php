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
 * @package     Magento_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Backend\Block\Page\System\Config\Robots\Reset
 */
namespace Magento\Backend\Block\Page\System\Config\Robots;

class ResetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Page\System\Config\Robots\Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var \Magento\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreConfigMock;

    protected function setUp()
    {
        $this->coreConfigMock = $this->getMock(
            'Magento\App\ConfigInterface', array(), array(), '', false
        );

        $this->_resetRobotsBlock = new Reset(
            $this->getMock('Magento\Backend\Block\Template\Context', array(), array(), '', false),
            $this->coreConfigMock,
            array()
        );
    }

    /**
     * @covers \Magento\Backend\Block\Page\System\Config\Robots\Reset::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $expectedInstructions = 'User-agent: *';
        $this->coreConfigMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($expectedInstructions));
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
