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
namespace Magento\OfflineShipping\Block\Adminhtml\Form\Field;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Config\Form\Field\Export
     */
    protected $_object;

    protected function setUp()
    {
        $backendUrl = $this->getMock('Magento\Backend\Model\UrlInterface', array(), array(), '', false, false);
        $backendUrl->expects($this->once())->method('getUrl')->with("*/*/exportTablerates", array('website' => 1));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $objectManager->getObject(
            'Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export',
            array('backendUrl' => $backendUrl)
        );
    }

    public function testGetElementHtml()
    {
        $expected = 'some test data';

        $form = $this->getMock('Magento\Framework\Data\Form', array('getParent'), array(), '', false, false);
        $parentObjectMock = $this->getMock(
            'Magento\Backend\Block\Template',
            array('getLayout'),
            array(),
            '',
            false,
            false
        );
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false, false);

        $blockMock = $this->getMock('Magento\Backend\Block\Widget\Button', array(), array(), '', false, false);

        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false, false);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->getMock('StdClass', array('toHtml'));
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $blockMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $blockMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));


        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));
        $parentObjectMock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));
        $form->expects($this->once())->method('getParent')->will($this->returnValue($parentObjectMock));

        $this->_object->setForm($form);
        $this->assertEquals($expected, $this->_object->getElementHtml());
    }
}
