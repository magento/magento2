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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Layout\Argument\Updater
 */
namespace Magento\Core\Model\Layout\Argument;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\Updater
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_argUpdaterMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_argUpdaterMock = $this->getMock('Magento\Core\Model\Layout\Argument\UpdaterInterface', array(), array(),
            '', false
        );

        $this->_model = new \Magento\Core\Model\Layout\Argument\Updater($this->_objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_argUpdaterMock);
        unset($this->_objectManagerMock);
    }

    public function testApplyUpdatersWithValidUpdaters()
    {
        $value = 1;

        $this->_objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->with($this->logicalOr('Dummy_Updater_1', 'Dummy_Updater_2'))
            ->will($this->returnValue($this->_argUpdaterMock));

        $this->_argUpdaterMock->expects($this->exactly(2))
            ->method('update')
            ->with($value)
            ->will($this->returnValue($value));

        $updaters = array('Dummy_Updater_1', 'Dummy_Updater_2');
        $this->assertEquals($value, $this->_model->applyUpdaters($value, $updaters));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testApplyUpdatersWithInvalidUpdaters()
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Dummy_Updater_1')
            ->will($this->returnValue(new \StdClass()));
        $updaters = array('Dummy_Updater_1', 'Dummy_Updater_2');

        $this->_model->applyUpdaters(1, $updaters);
    }
}
