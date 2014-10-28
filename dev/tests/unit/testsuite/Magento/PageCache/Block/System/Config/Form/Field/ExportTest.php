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
 * Page cache data helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Block\System\Config\Form\Field;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Block\System\Config\Form\Field\Export
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new StubExport();
    }

    /**
     * Test Case for Retrieving 'Export VCL' button HTML markup
     */
    public function testGetElementHtml()
    {
        $expected = 'some test data';
        $elementMock = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            array(),
            array(),
            '',
            false,
            false
        );

        $form = $this->getMock('Magento\Framework\Data\Form', array('getLayout'), array(), '', false, false);
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false, false);

        $buttonMock = $this->getMock('Magento\Backend\Block\Widget\Button', array(), array(), '', false, false);
        $urlBuilderMock = $this->getMock('Magento\Backend\Model\Url', array('getUrl'), array(), '', false, false);
        $urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/PageCache/exportVarnishConfig',
            array('website' => 1)
        )->will(
            $this->returnValue('/PageCache/exportVarnishConfig/')
        );
        $this->_model->setUrlBuilder($urlBuilderMock);

        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false, false);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->getMock('Magento\Framework\Object', array('toHtml'));
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $buttonMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $buttonMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($buttonMock));
        $form->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));

        $this->_model->setForm($form);
        $this->assertEquals($expected, $this->_model->getElementHtml($elementMock));
    }
}
