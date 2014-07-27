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
namespace Magento\Catalog\Controller\Adminhtml;

abstract class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Controller\Product
     */
    protected $action;

    /**
     *  Init context object
     */
    protected function initContext()
    {
        $productActionMock = $this->getMock('Magento\Catalog\Model\Product\Action', array(), array(), '', false);
        $objectManagerMock = $this->getMockForAbstractClass(
            '\Magento\Framework\ObjectManager',
            array(),
            '',
            true,
            true,
            true,
            array('get')
        );
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($productActionMock));

        $block = $this->getMockBuilder('\Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $layout = $this->getMockBuilder('Magento\Framework\View\Layout\Element\Layout')
            ->setMethods(['getBlock'])->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($block));
        $view = $this->getMockBuilder('Magento\Framework\App\View')
            ->setMethods(['loadLayout', 'getLayout', 'renderLayout'])
            ->disableOriginalConstructor()->getMock();
        $view->expects($this->any())->method('renderLayout')->will($this->returnSelf());
        $view->expects($this->any())->method('getLayout')->will($this->returnValue($layout));
        $view->expects($this->any())->method('loadLayout')->with(array(
            'popup',
            'catalog_product_new',
            'catalog_product_simple'
        ))->will($this->returnSelf());

        $eventManager = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->setMethods(['dispatch'])->disableOriginalConstructor()->getMock();
        $eventManager->expects($this->any())->method('dispatch')->will($this->returnSelf());
        $title = $this->getMockBuilder('\Magento\Framework\App\Action\Title')
            ->setMethods(['add'])->disableOriginalConstructor()->getMock();
        $title->expects($this->any())->method('add')->withAnyParameters()->will($this->returnSelf());
        $requestInterfaceMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')->setMethods(
            array('getParam', 'getFullActionName')
        )->disableOriginalConstructor()->getMock();

        $responseInterfaceMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')->setMethods(
            array('setRedirect', 'sendResponse')
        )->getMock();

        $managerInterfaceMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $sessionMock = $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false);
        $actionFlagMock = $this->getMock('Magento\Framework\App\ActionFlag', array(), array(), '', false);
        $helperDataMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            array(
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getEventManager',
                'getMessageManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getTitle',
                'getView'
            ),
            array(),
            '',
            false
        );

        $this->context->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($view));
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($requestInterfaceMock));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($responseInterfaceMock));
        $this->context->expects($this->any())->method('getObjectManager')->will($this->returnValue($objectManagerMock));

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($managerInterfaceMock));
        $this->context->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $this->context->expects($this->any())->method('getActionFlag')->will($this->returnValue($actionFlagMock));
        $this->context->expects($this->any())->method('getHelper')->will($this->returnValue($helperDataMock));
        return $this->context;
    }
}
