<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Select
     */
    protected $select;

    protected function setUp()
    {
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $escaper = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $context = $this->getMockBuilder('Magento\Framework\View\Element\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getEscaper')
            ->will($this->returnValue($escaper));
        $context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->select = $objectManagerHelper->getObject('Magento\Framework\View\Element\Html\Select', [
            'context' => $context
        ]);
    }

    public function testAddOptionAndSetOptionsAndGetOptions()
    {
        $value = 'testValue';
        $label = 'testLabel';
        $params = ['paramKey' => 'paramValue'];

        $options = [['value' => $value, 'label' => $label, 'params' => $params]];

        $this->assertEquals([], $this->select->getOptions());

        $this->select->addOption($value, $label, $params);
        $this->assertEquals($options, $this->select->getOptions());

        $options[0]['value'] = 'newValue';
        $this->select->setOptions($options);
        $this->assertEquals($options, $this->select->getOptions());
    }

    public function testGetSetId()
    {
        $selectId = 'testId';

        $this->assertNull($this->select->getId());
        $this->select->setId($selectId);
        $this->assertEquals($selectId, $this->select->getId());
    }

    public function testGetSetClass()
    {
        $selectClass = 'testClass';

        $this->assertNull($this->select->getClass());
        $this->select->setClass($selectClass);
        $this->assertEquals($selectClass, $this->select->getClass());
    }

    public function testGetSetTitle()
    {
        $selectTitle = 'testTitle';

        $this->assertNull($this->select->getTitle());
        $this->select->setTitle($selectTitle);
        $this->assertEquals($selectTitle, $this->select->getTitle());
    }

    public function testGetHtml()
    {
        $selectId = 'testId';
        $selectClass = 'testClass';
        $selectTitle = 'testTitle';
        $selectName = 'testName';

        $value = 'testValue';
        $label = 'testLabel';
        $params = ['paramKey' => 'paramValue'];

        $valueGroup = [
            'groupElementValue' => 'GroupElementLabel',
            'selectedGroupElementValue' => 'SelectedGroupElementLabel',
        ];
        $labelGroup = 'groupLabel';

        $selectedValue = 'selectedValue';
        $selectedLabel = 'selectedLabel';
        $selectedParams = [['paramKey' => 'paramValue', 'paramKey2' => 'paramValue2']];

        $selectedValues = [$selectedValue, 'selectedGroupElementValue'];

        $this->select->setId($selectId);
        $this->select->setClass($selectClass);
        $this->select->setTitle($selectTitle);
        $this->select->setName($selectName);
        $this->select->addOption($value, $label, $params);
        $this->select->addOption($selectedValue, $selectedLabel, $selectedParams);
        $this->select->addOption($valueGroup, $labelGroup);
        $this->select->setValue($selectedValues);

        $result = '<select name="testName" id="testId" class="testClass" title="testTitle" >'
            .   '<option value="testValue"  paramKey="paramValue" >testLabel</option>'
            .   '<option value="selectedValue" selected="selected"  paramKey="paramValue" '
            .       ' paramKey2="paramValue2" >selectedLabel</option>'
            .   '<optgroup label="groupLabel">'
            .       '<option value="groupElementValue" >GroupElementLabel</option>'
            .       '<option value="selectedGroupElementValue" selected="selected" >SelectedGroupElementLabel</option>'
            .   '</optgroup>'
            . '</select>';

        $this->assertEquals($result, $this->select->getHtml());
    }

    public function testGetHtmlJs()
    {
        $selectId = 'testId';
        $selectClass = 'testClass';
        $selectTitle = 'testTitle';
        $selectName = 'testName';

        $options = [
            'testValue' => 'testLabel',
            'selectedValue' => 'selectedLabel',
        ];
        $selectedValue = 'selectedValue';

        $this->select->setId($selectId);
        $this->select->setClass($selectClass);
        $this->select->setTitle($selectTitle);
        $this->select->setName($selectName);
        $this->select->setOptions($options);
        $this->select->setValue($selectedValue);

        $result = '<select name="testName" id="testId" class="testClass" title="testTitle" >'
            . '<option value="testValue" #{option_extra_attr_4016862802} >testLabel</option>'
            . '<option value="selectedValue" selected="selected" #{option_extra_attr_662265145} >selectedLabel</option>'
            . '</select>';

        $this->select->setIsRenderToJsTemplate(true);
        $this->assertEquals($result, $this->select->getHtml());
    }
}
