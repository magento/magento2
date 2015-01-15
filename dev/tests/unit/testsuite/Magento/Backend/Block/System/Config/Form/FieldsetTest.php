<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Config\Form;

class FieldsetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Config\Form\Fieldset
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_elementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var array
     */
    protected $_testData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_testHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            [],
            [],
            '',
            false,
            false
        );
        $this->_urlModelMock = $this->getMock('Magento\Backend\Model\Url', [], [], '', false, false);
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);
        $groupMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Group',
            [],
            [],
            '',
            false
        );
        $groupMock->expects($this->once())->method('getFieldsetCss')->will($this->returnValue('test_fieldset_css'));

        $this->_helperMock = $this->getMock('Magento\Framework\View\Helper\Js', [], [], '', false, false);

        $data = [
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlModelMock,
            'layout' => $this->_layoutMock,
            'jsHelper' => $this->_helperMock,
            'data' => ['group' => $groupMock],
        ];
        $this->_testHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $this->_testHelper->getObject('Magento\Backend\Block\System\Config\Form\Fieldset', $data);

        $this->_testData = [
            'htmlId' => 'test_field_id',
            'name' => 'test_name',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
            'legend' => 'test_legend',
            'comment' => 'test_comment',
        ];

        $this->_elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Text',
            ['getHtmlId', 'getName', 'getExpanded', 'getElements', 'getLegend', 'getComment'],
            [],
            '',
            false,
            false,
            true
        );

        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getHtmlId'
        )->will(
            $this->returnValue($this->_testData['htmlId'])
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getName'
        )->will(
            $this->returnValue($this->_testData['name'])
        );
        $this->_elementMock->expects($this->any())->method('getExpanded')->will($this->returnValue(true));
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getLegend'
        )->will(
            $this->returnValue($this->_testData['legend'])
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getComment'
        )->will(
            $this->returnValue($this->_testData['comment'])
        );
    }

    public function testRenderWithoutStoredElements()
    {
        $collection = $this->_testHelper->getObject('Magento\Framework\Data\Form\Element\Collection');
        $this->_elementMock->expects($this->any())->method('getElements')->will($this->returnValue($collection));
        $actualHtml = $this->_object->render($this->_elementMock);
        $this->assertContains($this->_testData['htmlId'], $actualHtml);
        $this->assertContains($this->_testData['legend'], $actualHtml);
        $this->assertContains($this->_testData['comment'], $actualHtml);
    }

    public function testRenderWithStoredElements()
    {
        $this->_helperMock->expects($this->any())->method('getScript')->will($this->returnArgument(0));

        $fieldMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Text',
            ['getId', 'getTooltip', 'toHtml'],
            [],
            '',
            false,
            false,
            true
        );

        $fieldMock->expects($this->any())->method('getId')->will($this->returnValue('test_field_id'));
        $fieldMock->expects($this->any())->method('getTooltip')->will($this->returnValue('test_field_tootip'));
        $fieldMock->expects($this->any())->method('toHtml')->will($this->returnValue('test_field_toHTML'));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $factoryColl = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $formMock = $this->getMock('Magento\Framework\Data\Form\AbstractForm', [], [$factory, $factoryColl]);
        $collection = $helper->getObject(
            'Magento\Framework\Data\Form\Element\Collection',
            ['container' => $formMock]
        );
        $collection->add($fieldMock);
        $this->_elementMock->expects($this->any())->method('getElements')->will($this->returnValue($collection));

        $actual = $this->_object->render($this->_elementMock);

        $this->assertContains('test_field_toHTML', $actual);

        $expected = '<div id="row_test_field_id_comment" class="system-tooltip-box"' .
            ' style="display:none;">test_field_tootip</div>';
        $this->assertContains($expected, $actual);
    }
}
