<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Tests for \Magento\Framework\Data\Form\Element\AbstractElement
 */
class AbstractElementTest extends TestCase
{
    private const RANDOM_STRING = '123456abcdefg';

    /**
     * @var AbstractElement|MockObject
     */
    protected $_model;

    /**
     * @var Factory|MockObject
     */
    protected $_factoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $_collectionFactoryMock;

    /**
     * @var Escaper|MockObject
     */
    protected $_escaperMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_factoryMock =
            $this->createMock(Factory::class);
        $this->_collectionFactoryMock =
            $this->createMock(CollectionFactory::class);
        $this->_escaperMock = $objectManager->getObject(Escaper::class);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn(self::RANDOM_STRING);

        $this->_model = $this->getMockForAbstractClass(
            AbstractElement::class,
            [
                $this->_factoryMock,
                $this->_collectionFactoryMock,
                $this->_escaperMock,
                [],
                $this->createMock(SecureHtmlRenderer::class),
                $randomMock
            ]
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::addElement()
     */
    public function testAddElement()
    {
        $elementId = 11;
        $elementMock = $this->getMockForAbstractClass(
            AbstractElement::class,
            [],
            '',
            false,
            true,
            true,
            ['getId']
        );
        $elementMock->expects($this->once())
            ->method('getId')
            ->willReturn($elementId);

        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->addMethods(['checkElementId', 'addElementToCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('checkElementId')
            ->with($elementId);
        $formMock->expects($this->once())
            ->method('addElementToCollection')
            ->with($elementMock);

        $collectionMock = $this->createMock(Collection::class);

        $this->_collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);

        $this->_model->setForm($formMock);
        $this->_model->addElement($elementMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtmlId()
     */
    public function testGetHtmlId()
    {
        $htmlIdPrefix = '--';
        $htmlIdSuffix = ']]';
        $htmlId = 'some_id';

        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->addMethods(['getHtmlIdPrefix', 'getHtmlIdSuffix'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->any())
            ->method('getHtmlIdPrefix')
            ->willReturn($htmlIdPrefix);
        $formMock->expects($this->any())
            ->method('getHtmlIdSuffix')
            ->willReturn($htmlIdSuffix);

        $this->_model->setId($htmlId);
        $this->_model->setForm($formMock);
        $this->assertEquals($htmlIdPrefix . $htmlId . $htmlIdSuffix, $this->_model->getHtmlId());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getName()
     */
    public function testGetNameWithoutSuffix()
    {
        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->addMethods(['getFieldNameSuffix', 'addSuffixToName'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->any())
            ->method('getFieldNameSuffix')
            ->willReturn(null);
        $formMock->expects($this->never())
            ->method('addSuffixToName');

        $this->_model->setForm($formMock);
        $this->_model->getName();
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getName()
     */
    public function testGetNameWithSuffix()
    {
        $returnValue = 'some_value';

        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->addMethods(['getFieldNameSuffix', 'addSuffixToName'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('getFieldNameSuffix')
            ->willReturn(true);
        $formMock->expects($this->once())
            ->method('addSuffixToName')
            ->willReturn($returnValue);

        $this->_model->setForm($formMock);

        $this->assertEquals($returnValue, $this->_model->getName());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::removeField()
     */
    public function testRemoveField()
    {
        $elementId = 'element_id';

        $formMock = $this->createPartialMock(AbstractForm::class, ['removeField']);
        $formMock->expects($this->once())
            ->method('removeField')
            ->with($elementId);

        $collectionMock = $this->createPartialMock(Collection::class, ['remove']);
        $collectionMock->expects($this->once())
            ->method('remove')
            ->with($elementId);

        $this->_collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);

        $this->_model->setForm($formMock);
        $this->_model->removeField($elementId);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtmlAttributes()
     */
    public function testGetHtmlAttributes()
    {
        $htmlAttributes = [
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'autocomplete',
            'tabindex',
            'placeholder',
            'data-form-part',
            'data-role',
            'data-action',
            'checked',
        ];
        $this->assertEquals($htmlAttributes, $this->_model->getHtmlAttributes());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::addClass()
     */
    public function testAddClass()
    {
        $oldClass = 'old_class';
        $newClass = 'new_class';
        $this->_model->addClass($oldClass);
        $this->_model->addClass($newClass);

        $this->assertEquals(' ' . $oldClass . ' ' . $newClass, $this->_model->getClass());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::removeClass()
     */
    public function testRemoveClass()
    {
        $oldClass = 'old_class';
        $newClass = 'new_class';
        $oneMoreClass = 'some_class';
        $this->_model->addClass($oldClass);
        $this->_model->addClass($oneMoreClass);
        $this->_model->addClass($newClass);

        $this->_model->removeClass($oneMoreClass);

        $this->assertEquals(' ' . $oldClass . ' ' . $newClass, $this->_model->getClass());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getEscapedValue()
     */
    public function testGetEscapedValueWithoutFilter()
    {
        $this->_model->setValue('<a href="#hash_tag">my &#039;quoted&#039; string</a>');
        $this->assertEquals(
            '&lt;a href=&quot;#hash_tag&quot;&gt;my &#039;quoted&#039; string&lt;/a&gt;',
            $this->_model->getEscapedValue()
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getEscapedValue()
     */
    public function testGetEscapedValueWithFilter()
    {
        $value = '<a href="#hash_tag">my &#039;quoted&#039; string</a>';
        $expectedValue = '&lt;a href=&quot;#hash_tag&quot;&gt;my &#039;quoted&#039; string&lt;/a&gt;';

        $filterMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('filter')
            ->with($value)
            ->willReturnArgument(0);

        $this->_model->setValueFilter($filterMock);
        $this->_model->setValue($value);
        $this->assertEquals($expectedValue, $this->_model->getEscapedValue());
    }

    /**
     * @param array $initialData
     * @param string $expectedValue
     * @dataProvider getElementHtmlDataProvider
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getElementHtml()
     */
    public function testGetElementHtml(array $initialData, $expectedValue)
    {
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );

        $this->_model->setData($initialData);
        $this->assertEquals($expectedValue, $this->_model->getElementHtml());
    }

    /**
     * @param array $initialData
     * @param string $expectedValue
     * @dataProvider getLabelHtmlDataProvider
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getLabelHtml()
     */
    public function testGetLabelHtml(array $initialData, $expectedValue)
    {
        $idSuffix = isset($initialData['id_suffix']) ? $initialData['id_suffix'] : null;
        $this->_model->setData($initialData);
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );
        $this->assertEquals($expectedValue, $this->_model->getLabelHtml($idSuffix));
    }

    /**
     * @param array $initialData
     * @param string $expectedValue
     * @dataProvider testGetDefaultHtmlDataProvider
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getDefaultHtml()
     */
    public function testGetDefaultHtml(array $initialData, $expectedValue)
    {
        $this->_model->setData($initialData);
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );
        $this->assertEquals($expectedValue, $this->_model->getDefaultHtml());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtml()
     */
    public function testGetHtmlWithoutRenderer()
    {
        $this->_model->setRequired(true);
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );
        $expectedHtml = '<div class="admin__field">'
            . "\n"
            . '<input id="" name=""  data-ui-id="form-element-" value="" class=" required-entry _required"'
            .' formelementhookid="elemId' .self::RANDOM_STRING .'"/></div>'
            . "\n";

        $this->assertEquals($expectedHtml, $this->_model->getHtml());
        $this->assertEquals(' required-entry _required', $this->_model->getClass());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtml()
     */
    public function testGetHtmlWithRenderer()
    {
        $this->_model->setRequired(true);

        $expectedHtml = 'some-html';

        $rendererMock = $this->getMockForAbstractClass(
            RendererInterface::class
        );
        $rendererMock->expects($this->once())
            ->method('render')
            ->with($this->_model)
            ->willReturn($expectedHtml);
        $this->_model->setRenderer($rendererMock);

        $this->assertEquals($expectedHtml, $this->_model->getHtml());
        $this->assertEquals(' required-entry _required', $this->_model->getClass());
    }

    /**
     * @param array $initialData
     * @param string $expectedValue
     * @dataProvider serializeDataProvider
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::serialize()
     */
    public function testSerialize(array $initialData, $expectedValue)
    {
        $attributes = [];
        if (isset($initialData['attributes'])) {
            $attributes = $initialData['attributes'];
            unset($initialData['attributes']);
        }
        $this->_model->setData($initialData);
        $expectedValue .= ' formelementhookid="elemId' .self::RANDOM_STRING .'"';
        $this->assertEquals(trim($expectedValue), $this->_model->serialize($attributes));
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtmlContainerId()
     */
    public function testGetHtmlContainerIdWithoutId()
    {
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );
        $this->assertEquals('', $this->_model->getHtmlContainerId());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtmlContainerId()
     */
    public function testGetHtmlContainerIdWithContainerId()
    {
        $containerId = 'some-id';
        $this->_model->setContainerId($containerId);
        $this->_model->setForm(
            $this->createMock(AbstractForm::class)
        );
        $this->assertEquals($containerId, $this->_model->getHtmlContainerId());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::getHtmlContainerId()
     */
    public function testGetHtmlContainerIdWithFieldContainerIdPrefix()
    {
        $id = 'id';
        $prefix = 'prefix_';
        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->addMethods(['getFieldContainerIdPrefix'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('getFieldContainerIdPrefix')
            ->willReturn($prefix);

        $this->_model->setId($id);
        $this->_model->setForm($formMock);
        $this->assertEquals($prefix . $id, $this->_model->getHtmlContainerId());
    }

    /**
     * @param array $initialData
     * @param string $expectedValue
     * @dataProvider addElementValuesDataProvider
     * @covers \Magento\Framework\Data\Form\Element\AbstractElement::addElementValues()
     */
    public function testAddElementValues(array $initialData, $expectedValue)
    {
        $this->_model->setValues($initialData['initial_values']);
        $this->_model->addElementValues($initialData['add_values'], $initialData['overwrite']);

        $this->assertEquals($expectedValue, $this->_model->getValues());
    }

    /**
     * @return array
     */
    public function addElementValuesDataProvider()
    {
        return [
            [
                [
                    'initial_values' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'add_values' => [
                        'key_1' => 'value_4',
                        'key_2' => 'value_5',
                        'key_3' => 'value_6',
                        'key_4' => 'value_7',
                    ],
                    'overwrite' => false,
                ],
                [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                    'key_4' => 'value_7'
                ],
            ],
            [
                [
                    'initial_values' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'add_values' => [
                        'key_1' => 'value_4',
                        'key_2' => 'value_5',
                        'key_3' => 'value_6',
                        'key_4' => 'value_7',
                    ],
                    'overwrite' => true,
                ],
                [
                    'key_1' => 'value_4',
                    'key_2' => 'value_5',
                    'key_3' => 'value_6',
                    'key_4' => 'value_7'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function serializeDataProvider()
    {
        return [
            [
                [],
                '',
            ],
            [
                [
                    'attributes' => ['disabled'],
                    'disabled' => true,
                ],
                'disabled="disabled"'
            ],
            [
                [
                    'attributes' => ['checked'],
                    'checked' => true,
                ],
                'checked="checked"'
            ],
            [
                [
                    'data-locked' => 1,
                    'attributes' => ['attribute_1'],
                ],
                'data-locked="1"'
            ]
        ];
    }

    /**
     * @return array
     */
    public function testGetDefaultHtmlDataProvider()
    {
        return [
            [
                [],
                '<div class="admin__field">' . "\n"
                . '<input id="" name=""  data-ui-id="form-element-" value=""'
                .' formelementhookid="elemId' .self::RANDOM_STRING .'"/></div>' . "\n",
            ],
            [
                ['default_html' => 'some default html'],
                'some default html'
            ],
            [
                [
                    'label' => 'some label',
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                ],
                '<div class="admin__field">' . "\n"
                . '<label class="label admin__field-label" for="html-id" data-ui-id="form-element-some-namelabel">'
                . '<span>some label</span></label>' . "\n"
                . '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>'
                . '</div>' . "\n"
            ],
            [
                [
                    'label' => 'some label',
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                    'no_span' => true,
                ],
                '<label class="label admin__field-label" for="html-id" data-ui-id="form-element-some-namelabel">'
                . '<span>some label</span></label>' . "\n"
                . '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getLabelHtmlDataProvider()
    {
        return [
            [
                [],
                '',
            ],
            [
                [
                    'id_suffix' => 'suffix',
                ],
                ''
            ],
            [
                [
                    'label' => 'some-label',
                    'html_id' => 'some-html-id',
                ],
                '<label class="label admin__field-label" for="some-html-id" data-ui-id="form-element-label">'
                . '<span>some-label</span></label>' . "\n"
            ],
            [
                [
                    'id_suffix' => 'suffix',
                    'label' => 'some-label',
                    'html_id' => 'some-html-id',
                ],
                '<label class="label admin__field-label" for="some-html-idsuffix" data-ui-id="form-element-label">'
                . '<span>some-label</span></label>' . "\n"
            ],
        ];
    }

    /**
     * @return array
     */
    public function getElementHtmlDataProvider()
    {
        return [
            [
                [],
                '<input id="" name=""  data-ui-id="form-element-" value="" formelementhookid="elemId'
                    .self::RANDOM_STRING .'"/>',
            ],
            [
                [
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                ],
                '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                    .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>'
            ],
            [
                [
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                    'before_element_html' => 'some-html',
                ],
                '<label class="addbefore" for="html-id">some-html</label>'
                . '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>'
            ],
            [
                [
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                    'after_element_js' => 'some-js',
                ],
                '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                    .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>some-js'
            ],
            [
                [
                    'html_id' => 'html-id',
                    'name' => 'some-name',
                    'value' => 'some-value',
                    'after_element_html' => 'some-html',
                ],
                '<input id="html-id" name="some-name"  data-ui-id="form-element-some-name" value="some-value"'
                    .' formelementhookid="elemId' .self::RANDOM_STRING .'"/>'
                    . '<label class="addafter" for="html-id">some-html</label>'
            ]
        ];
    }
}
