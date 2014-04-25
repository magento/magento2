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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

class QtyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty
     */
    protected $renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->rowMock = $this->getMock('Magento\Framework\Object', array('getTypeId', 'getIndex'), array(), '', false);
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->renderer = $helper->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty',
            array('typeConfig' => $this->typeConfigMock)
        );
    }

    public function testRender()
    {
        $expected = '<input type="text" name="id_name" value="" disabled="disabled" ' .
            'class="input-text inline_css input-inactive" />';
        $this->typeConfigMock->expects(
            $this->any()
        )->method(
            'isProductSet'
        )->with(
            'id'
        )->will(
            $this->returnValue(true)
        );
        $this->rowMock->expects($this->once())->method('getTypeId')->will($this->returnValue('id'));
        $columnMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            array('getInlineCss', 'getId'),
            array(),
            '',
            false
        );
        $this->renderer->setColumn($columnMock);

        $columnMock->expects($this->once())->method('getId')->will($this->returnValue('id_name'));
        $columnMock->expects($this->once())->method('getInlineCss')->will($this->returnValue('inline_css'));

        $this->assertEquals($expected, $this->renderer->render($this->rowMock));
    }
}
