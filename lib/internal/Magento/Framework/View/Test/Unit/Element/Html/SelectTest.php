<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    /**
     * @var Select
     */
    protected $select;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    protected function setUp(): void
    {
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaper);
        $context->expects($this->once())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($scopeConfig);

        $objectManagerHelper = new ObjectManager($this);
        $this->select = $objectManagerHelper->getObject(
            Select::class,
            ['context' => $context]
        );
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

    public function testGetHtmlJs()
    {
        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $this->escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturnArgument(0);

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
            . '<option value="testValue" <%= option_extra_attrs.option_4016862802 %> >testLabel</option>'
            . '<option value="selectedValue" selected="selected" <%= option_extra_attrs.option_662265145 %> >'
            . 'selectedLabel</option>'
            . '</select>';

        $this->select->setIsRenderToJsTemplate(true);
        $this->assertEquals($result, $this->select->getHtml());
    }

    public function testGetHtml()
    {
        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $this->escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturnArgument(0);

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
            . '<option value="testValue"  paramKey="paramValue" >testLabel</option>'
            . '<option value="selectedValue" selected="selected"  paramKey="paramValue" '
            . ' paramKey2="paramValue2" >selectedLabel</option>'
            . '<optgroup label="groupLabel" data-optgroup-name="groupLabel">'
            . '<option value="groupElementValue" >GroupElementLabel</option>'
            . '<option value="selectedGroupElementValue" selected="selected" >SelectedGroupElementLabel</option>'
            . '</optgroup>'
            . '</select>';

        $this->assertEquals($result, $this->select->getHtml());
    }

    public function testGetHtmlEscapes()
    {
        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturn('ESCAPED');
        $this->escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturn('ESCAPED_ATTR');

        $optionsSets = [
            $this->getOptionsWithSingleQuotes(),
            $this->getOptionsWithDoubleQuotes()
        ];

        $expectedResult = '<select name="test[name]" id="testId" class="test class" title="ESCAPED" >'
            . '<option value="ESCAPED"  paramKey="ESCAPED" >ESCAPED</option>'
            . '<option value="ESCAPED" selected="selected" >ESCAPED</option>'
            . '<optgroup label="ESCAPED" data-optgroup-name="ESCAPED">'
            . '<option value="ESCAPED" >ESCAPED</option>'
            . '<option value="ESCAPED" selected="selected" >ESCAPED</option>'
            . '</optgroup>'
            . '</select>';

        foreach ($optionsSets as $inOptions) {
            $this->select->setId($inOptions['id']);
            $this->select->setClass($inOptions['class']);
            $this->select->setTitle($inOptions['title']);
            $this->select->setName($inOptions['name']);

            foreach ($inOptions['options'] as $option) {
                $this->select->addOption($option['value'], $option['label'], $option['params']);
            }
            $this->select->setValue($inOptions['values']);

            $this->assertEquals($expectedResult, $this->select->getHtml());

            // reset
            $this->select->setOptions([]);
        }
    }

    /**
     * @return array
     */
    private function getOptionsWithSingleQuotes()
    {
        return [
            'id' => "testId",
            'name' => "test[name]",
            'class' => "test class",
            'title' => "test'Title",
            'options' => [
                'regular' => [
                    'value' => 'testValue',
                    'label' => "test'Label",
                    'params' => ['paramKey' => "param'Value"]
                ],
                'selected' => [
                    'value' => 'selectedValue',
                    'label' => "selected'Label",
                    'params' => []
                ],
                'optgroup' => [
                    'value' => [
                        'groupElementValue' => "GroupElement'Label",
                        'selectedGroupElementValue' => "SelectedGroupElement'Label"
                    ],
                    'label' => "group'Label",
                    'params' => []
                ]
            ],
            'values' => ['selectedValue', 'selectedGroupElementValue']
        ];
    }

    /**
     * @return array
     */
    private function getOptionsWithDoubleQuotes()
    {
        return [
            'id' => 'testId',
            'name' => 'test[name]',
            'class' => 'test class',
            'title' => 'test"Title',
            'options' => [
                'regular' => [
                    'value' => 'testValue',
                    'label' => 'test"Label',
                    'params' => ['paramKey' => 'param"Value']
                ],
                'selected' => [
                    'value' => 'selectedValue',
                    'label' => 'selected"Label',
                    'params' => []
                ],
                'optgroup' => [
                    'value' => [
                        'groupElementValue' => 'GroupElement"Label',
                        'selectedGroupElementValue' => 'SelectedGroupElement"Label'
                    ],
                    'label' => 'group"Label',
                    'params' => []
                ]
            ],
            'values' => ['selectedValue', 'selectedGroupElementValue']
        ];
    }
}
