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
namespace Magento\Log\Model\Shell\Command;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Log\Model\Shell\Command\Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Log\Model\Shell\Command\Factory($this->_objectManagerMock);
    }

    public function testCreateCleanCommand()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Log\Model\Shell\Command\Clean',
            array('days' => 1)
        )->will(
            $this->returnValue($this->getMock('Magento\Log\Model\Shell\Command\Clean', array(), array(), '', false))
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createCleanCommand(1));
    }

    public function testCreateStatusCommand()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Log\Model\Shell\Command\Status'
        )->will(
            $this->returnValue($this->getMock('Magento\Log\Model\Shell\Command\Status', array(), array(), '', false))
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createStatusCommand());
    }
}
