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
namespace Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invocationChainMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin\Configurable
     */
    protected $model;

    protected function setUp()
    {
        $this->requestMock = $this->getMock(
            'Magento\App\Request\Http',
            array(),
            array(),
            '',
            false
        );
        $this->model = new Configurable($this->requestMock);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('setTypeId', '__wakeup'),
            array(),
            '',
            false
        );
        $this->invocationChainMock = $this->getMock('Magento\Code\Plugin\InvocationChain', array(), array(), '', false);
    }

    public function testAroundProcessProductWithProductThatCanBeTransformedToConfigurable()
    {
        $this->requestMock->expects($this->any())->method('getParam')->with('attributes')
            ->will($this->returnValue('not_empty_attribute_data'));
        $this->productMock->expects($this->once())->method('setTypeId')
            ->with(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->invocationChainMock->expects($this->never())->method('proceed');
        $this->model->aroundProcessProduct(array($this->productMock), $this->invocationChainMock);
    }

    public function testAroundProcessProductWithProductThatCannotBeTransformedToConfigurable()
    {
        $this->requestMock->expects($this->any())->method('getParam')->with('attributes')
            ->will($this->returnValue(null));
        $this->productMock->expects($this->never())->method('setTypeId');
        $arguments = array($this->productMock);
        $this->invocationChainMock->expects($this->once())->method('proceed')->with($arguments);
        $this->model->aroundProcessProduct($arguments, $this->invocationChainMock);
    }
}
