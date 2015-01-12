<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test backend controller for the theme
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

abstract class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \Magento\Theme\Controller\Adminhtml\System\Design\Theme
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->_request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->eventManager = $this->getMock('\Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->view = $this->getMock('\Magento\Framework\App\ViewInterface', [], [], '', false);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Theme\Controller\Adminhtml\System\Design\Theme\\' . $this->name,
            [
                'request' => $this->_request,
                'objectManager' => $this->_objectManagerMock,
                'response' => $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false),
                'eventManager' => $this->eventManager,
                'view' => $this->view,
            ]
        );
    }
}
