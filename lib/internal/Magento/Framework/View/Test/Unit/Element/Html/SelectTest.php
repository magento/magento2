<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html;

use \Magento\Framework\View\Element\Html\Select;
use Magento\Framework\Escaper;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Select
     */
    protected $select;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaper;

    protected function setUp()
    {
        $eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);

        $scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));
        $this->escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->will($this->returnArgument(0));

        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getEscaper')
            ->will($this->returnValue($this->escaper));
        $context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->select = $objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Html\Select::class,
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
            .   '<optgroup label="groupLabel" data-optgroup-name="groupLabel">'
            .       '<option value="groupElementValue" >GroupElementLabel</option>'
            .       '<option value="selectedGroupElementValue" selected="selected" >SelectedGroupElementLabel</option>'
            .   '</optgroup>'
            . '</select>';

        $this->assertEquals($result, $this->select->getHtml());
    }

    public function testGetHtmlEscapes()
    {
        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnValue('ESCAPED'));
        $this->escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->will($this->returnValue('ESCAPED_ATTR'));

        $optionsSets = [
            'with-single-quote' => [
                'in' => [
                    'id' => "test'Id",
                    'class' => "test'Class",
                    'title' => "test'Title",
                    'name' => "test'Name",
                    'options' => [
                        'regular' => [
                            'value' => 'testValue',
                            'label' => "test'Label",
                            'params' => ['paramKey' => "param'Value"]
                        ],
                        'selected' => [
                            'value' => 'selectedValue',
                            'label' => "selected'Label"
                        ],
                        'optgroup' => [
                            'value' => [
                                'groupElementValue' => "GroupElement'Label",
                                'selectedGroupElementValue' => "SelectedGroupElement'Label"
                            ],
                            'label' => "group'Label"
                        ]
                    ],
                    'values' => ['selectedValue', 'selectedGroupElementValue']
                ],
                'out' => [
                    'id' => 'ESCAPED_ATTR',
                    'class' => 'ESCAPED_ATTR',
                    'title' => 'ESCAPED_ATTR',
                    'name' => 'ESCAPED_ATTR',
                    'options' => [
                        'regular' => [
                            'value' => 'testValue',
                            'label' => 'ESCAPED',
                            'params' => ['paramKey' => 'ESCAPED_ATTR']
                        ],
                        'selected' => [
                            'value' => 'selectedValue',
                            'label' => 'ESCAPED'
                        ],
                        'optgroup' => [
                            'value' => [
                                'groupElementValue' => 'ESCAPED',
                                'selectedGroupElementValue' => 'ESCAPED'
                            ],
                            'label' => 'ESCAPED_ATTR'
                        ]
                    ],
                    'values' => ['selectedValue', 'selectedGroupElementValue']
                ]
            ],
            'with-double-quote' => [
                'in' => [
                    'id' => 'test"Id',
                    'class' => 'test"Class',
                    'title' => 'test"Title',
                    'name' => 'test"Name',
                    'options' => [
                        'regular' => [
                            'value' => 'testValue',
                            'label' => 'test"Label',
                            'params' => ['paramKey' => 'param"Value']
                        ],
                        'selected' => [
                            'value' => 'selectedValue',
                            'label' => 'selected"Label'
                        ],
                        'optgroup' => [
                            'value' => [
                                'groupElementValue' => 'GroupElement"Label',
                                'selectedGroupElementValue' => 'SelectedGroupElement"Label'
                            ],
                            'label' => 'group"Label'
                        ]
                    ],
                    'values' => ['selectedValue', 'selectedGroupElementValue']
                ],
                'out' => [
                    'id' => 'ESCAPED_ATTR',
                    'class' => 'ESCAPED_ATTR',
                    'title' => 'ESCAPED_ATTR',
                    'name' => 'ESCAPED_ATTR',
                    'options' => [
                        'regular' => [
                            'value' => 'testValue',
                            'label' => 'ESCAPED',
                            'params' => ['paramKey' => 'ESCAPED_ATTR']
                        ],
                        'selected' => [
                            'value' => 'selectedValue',
                            'label' => 'ESCAPED'
                        ],
                        'optgroup' => [
                            'value' => [
                                'groupElementValue' => 'ESCAPED',
                                'selectedGroupElementValue' => 'ESCAPED'
                            ],
                            'label' => 'ESCAPED_ATTR'
                        ]
                    ],
                    'values' => ['selectedValue', 'selectedGroupElementValue']
                ]
            ]
        ];

        foreach ($optionsSets as $optionsSet) {
            $inOptions = $optionsSet['in'];

            $this->select->setId($inOptions['id']);
            $this->select->setClass($inOptions['class']);
            $this->select->setTitle($inOptions['title']);
            $this->select->setName($inOptions['name']);

            foreach ($inOptions['options'] as $option) {
                $this->select->addOption($option['value'], $option['label'], $option['params']);
            }
            $this->select->setValue($inOptions['values']);

            $result = $this->prepareResult($optionsSet['out']);

            $this->assertEquals($result, $this->select->getHtml());

            // reset
            $this->select->setOptions([]);
        }
    }

    private function prepareResult(array $optionSet)
    {
        $result = '<select name="ESCAPED_ATTR" id="ESCAPED_ATTR" class="ESCAPED_ATTR" title="ESCAPED_ATTR" >'
            .   '<option value="'.$optionSet['options']['regular']['value'].'"  paramKey="ESCAPED_ATTR" >ESCAPED</option>'
            .   '<option value="'.$optionSet['options']['selected']['value'].'" selected="selected" >ESCAPED</option>'
            .   '<optgroup label="ESCAPED_ATTR" data-optgroup-name="ESCAPED_ATTR">'
            .       '<option value="groupElementValue" >ESCAPED</option>'
            .       '<option value="selectedGroupElementValue" selected="selected" >ESCAPED</option>'
            .   '</optgroup>'
            . '</select>';

        return $result;
    }
}
