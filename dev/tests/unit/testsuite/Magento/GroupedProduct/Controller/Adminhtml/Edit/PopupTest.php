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
namespace Magento\GroupedProduct\Controller\Adminhtml\Edit;

class PopupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false);
        $this->factory = $this->getMock('Magento\Catalog\Model\ProductFactory', array('create'), array(), '', false);
        $this->registry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->view = $this->getMock('Magento\Framework\App\ViewInterface', array(), array(), '', false);

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->action = $this->objectManager->getObject(
            '\Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup',
            array(
                'request' => $this->request,
                'factory' => $this->factory,
                'registry' => $this->registry,
                'view' => $this->view
            )
        );
    }

    public function testPopupActionNoProductId()
    {
        $storeId = 12;
        $typeId = 4;
        $productId = null;
        $setId = 0;
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('setStoreId', 'setTypeId', 'setData', '__wakeup'),
            array(),
            '',
            false
        );

        $this->request->expects($this->at(0))->method('getParam')->with('id')->will($this->returnValue($productId));
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($product));
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            'store',
            0
        )->will(
            $this->returnValue($storeId)
        );

        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $this->request->expects($this->at(2))->method('getParam')->with('type')->will($this->returnValue($typeId));
        $product->expects($this->once())->method('setTypeId')->with($typeId);
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->request->expects($this->at(3))->method('getParam')->with('set')->will($this->returnValue($setId));
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->view->expects($this->once())->method('loadLayout')->with(false);
        $this->view->expects($this->once())->method('renderLayout');

        $this->action->execute();
    }

    public function testPopupActionWithProductIdNoSetId()
    {
        $storeId = 12;
        $typeId = 4;
        $setId = 0;
        $productId = 399;
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('setStoreId', 'setTypeId', 'setData', 'load', '__wakeup'),
            array(),
            '',
            false
        );

        $this->request->expects($this->at(0))->method('getParam')->with('id')->will($this->returnValue($productId));
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($product));
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            'store',
            0
        )->will(
            $this->returnValue($storeId)
        );
        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $this->request->expects($this->at(2))->method('getParam')->with('type')->will($this->returnValue($typeId));
        $product->expects($this->never())->method('setTypeId');
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $product->expects($this->once())->method('load')->with($productId);
        $this->request->expects($this->at(3))->method('getParam')->with('set')->will($this->returnValue($setId));
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->view->expects($this->once())->method('loadLayout')->with(false);
        $this->view->expects($this->once())->method('renderLayout');

        $this->action->execute();
    }
}
