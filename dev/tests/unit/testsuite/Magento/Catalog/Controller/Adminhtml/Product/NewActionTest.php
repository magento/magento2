<?php
/**
 *
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
namespace Magento\Catalog\Controller\Adminhtml\Product;

class NewActionTest extends \Magento\Catalog\Controller\Adminhtml\ProductTest
{
    protected $action;

    protected function setUp()
    {
        $productBuilder = $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Builder')->setMethods([
                'build'
            ])->disableOriginalConstructor()->getMock();

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $productBuilder->expects($this->any())->method('build')->will($this->returnValue($product));

        $this->action = new \Magento\Catalog\Controller\Adminhtml\Product\NewAction(
            $this->initContext(),
            $productBuilder,
            $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter')
                ->disableOriginalConstructor()->getMock()
        );

    }

    /**
     * Testing `newAction` method
     */
    public function testExecute()
    {
        $this->action->getRequest()->expects($this->at(0))->method('getParam')
            ->with('set')->will($this->returnValue(true));
        $this->action->getRequest()->expects($this->at(1))->method('getParam')
            ->with('popup')->will($this->returnValue(true));
        $this->action->getRequest()->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalog_product_new'));
        $this->action->execute();
    }
}
