<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Url;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Layout;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FieldsetTest extends TestCase
{
    /**
     * @var Fieldset
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_elementMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_urlModelMock;

    /**
     * @var array
     */
    protected $testData = [
        'htmlId' => 'test_field_id',
        'name' => 'test_name',
        'label' => 'test_label',
        'elementHTML' => 'test_html',
        'legend' => 'test_legend',
        'comment' => 'test_comment',
        'tooltip'     => 'test_tooltip',
    ];

    /**
     * @var MockObject
     */
    protected $_layoutMock;

    /**
     * @var ObjectManager
     */
    protected $_testHelper;

    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $authSessionMock;

    /**
     * @var MockObject
     */
    protected $userMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->userMock = $this->getMockBuilder(User::class)
            ->setMethods(['getExtra'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock);

        $this->_requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn('Test Param');
        $this->_urlModelMock = $this->createMock(Url::class);
        $this->_layoutMock = $this->createMock(Layout::class);
        $groupMock = $this->createMock(Group::class);
        $groupMock->expects($this->any())->method('getFieldsetCss')->willReturn('test_fieldset_css');

        $this->_helperMock = $this->createMock(Js::class);
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderStyleAsTag')
            ->willReturnCallback(
                function (string $style, string $selector): string {
                    return "<style>$selector { $style }</style>";
                }
            );

        $data = [
            'request' => $this->_requestMock,
            'authSession' => $this->authSessionMock,
            'urlBuilder' => $this->_urlModelMock,
            'layout' => $this->_layoutMock,
            'jsHelper' => $this->_helperMock,
            'data' => ['group' => $groupMock],
            'secureRenderer' => $secureRendererMock
        ];
        $this->_testHelper = new ObjectManager($this);
        $this->_object = $this->_testHelper->getObject(Fieldset::class, $data);

        $this->_elementMock = $this->getMockBuilder(Text::class)
            ->addMethods(['getLegend', 'getComment', 'getIsNested', 'getExpanded'])
            ->onlyMethods(['getId', 'getHtmlId', 'getName', 'getElements', 'getForm'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_elementMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->testData['htmlId']);
        $this->_elementMock->expects($this->any())
            ->method('getHtmlId')
            ->willReturn($this->testData['htmlId']);
        $this->_elementMock->expects($this->any())
            ->method('getName')
            ->willReturn($this->testData['name']);
        $this->_elementMock->expects($this->any())
            ->method('getLegend')
            ->willReturn($this->testData['legend']);
        $this->_elementMock->expects($this->any())
            ->method('getComment')
            ->willReturn($this->testData['comment']);
    }

    /**
     * @param $expanded
     * @param $nested
     * @param extra
     * @dataProvider renderWithoutStoredElementsDataProvider
     */
    public function testRenderWithoutStoredElements($expanded, $nested, $extra)
    {
        $this->userMock->expects($this->any())->method('getExtra')->willReturn($extra);
        $collection = $this->_testHelper->getObject(Collection::class);
        $formMock = $this->createMock(Form::class);
        $this->_elementMock->expects($this->any())->method('getForm')->willReturn($formMock);
        $formMock->expects($this->any())->method('getElements')->willReturn($collection);
        $this->_elementMock->expects($this->any())->method('getElements')->willReturn($collection);
        $this->_elementMock->expects($this->any())->method('getIsNested')->willReturn($nested);
        $this->_elementMock->expects($this->any())->method('getExpanded')->willReturn($expanded);
        $actualHtml = $this->_object->render($this->_elementMock);
        $this->assertStringContainsString($this->testData['htmlId'], $actualHtml);
        $this->assertStringContainsString($this->testData['legend'], $actualHtml);
        $this->assertStringContainsString($this->testData['comment'], $actualHtml);
        if ($nested) {
            $this->assertStringContainsString('nested', $actualHtml);
        }
    }

    /**
     * @param $expanded
     * @param $nested
     * @param $extra
     * @dataProvider renderWithStoredElementsDataProvider
     */
    public function testRenderWithStoredElements($expanded, $nested, $extra)
    {
        $this->userMock->expects($this->any())->method('getExtra')->willReturn($extra);
        $this->_helperMock->expects($this->any())->method('getScript')->willReturnArgument(0);
        $fieldMock = $this->getMockBuilder(Text::class)
            ->setMethods(['getId', 'getTooltip', 'toHtml', 'getHtmlId', 'getIsNested', 'getExpanded'])
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMock->expects($this->any())->method('getId')->willReturn('test_field_id');
        $fieldMock->expects($this->any())->method('getTooltip')->willReturn('test_field_tootip');
        $fieldMock->expects($this->any())->method('toHtml')->willReturn('test_field_toHTML');
        $fieldMock->expects($this->any())->method('getHtmlId')->willReturn('test_field_HTML_id');

        $fieldSetMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Fieldset::class)
            ->setMethods(['getId', 'getTooltip', 'toHtml', 'getHtmlId', 'getIsNested', 'getExpanded'])
            ->disableOriginalConstructor()
            ->getMock();
        $fieldSetMock->expects($this->any())->method('getId')->willReturn('test_fieldset_id');
        $fieldSetMock->expects($this->any())->method('getTooltip')->willReturn('test_fieldset_tootip');
        $fieldSetMock->expects($this->any())->method('toHtml')->willReturn('test_fieldset_toHTML');
        $fieldSetMock->expects($this->any())->method('getHtmlId')->willReturn('test_fieldset_HTML_id');

        $factory = $this->createMock(Factory::class);

        $factoryColl = $this->createMock(CollectionFactory::class);

        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->setConstructorArgs([$factory, $factoryColl])
            ->getMock();

        $collection = $this->_testHelper->getObject(
            Collection::class,
            ['container' => $formMock]
        );
        $collection->add($fieldMock);
        $collection->add($fieldSetMock);
        $formMock = $this->createMock(Form::class);
        $this->_elementMock->expects($this->any())->method('getForm')->willReturn($formMock);
        $formMock->expects($this->any())->method('getElements')->willReturn($collection);
        $this->_elementMock->expects($this->any())->method('getElements')->willReturn($collection);
        $this->_elementMock->expects($this->any())->method('getIsNested')->willReturn($nested);
        $this->_elementMock->expects($this->any())->method('getExpanded')->willReturn($expanded);

        $actual = $this->_object->render($this->_elementMock);

        $this->assertStringContainsString('test_field_toHTML', $actual);

        $expected = '<div id="row_test_field_id_comment" class="system-tooltip-box">test_field_tootip</div>' .
        '<style>#row_test_field_id_comment { display:none; }</style>';
        $this->assertStringContainsString($expected, $actual);
        if ($nested) {
            $this->assertStringContainsString('nested', $actual);
        }
    }

    /**
     * @return array
     */
    public function renderWithoutStoredElementsDataProvider()
    {
        return $this->dataProvider();
    }

    /**
     * @return array
     */
    public function renderWithStoredElementsDataProvider()
    {
        return $this->dataProvider();
    }

    /**
     * @return array
     */
    protected function dataProvider()
    {
        return [
            'expandedNestedExtra' => [
                'expanded' => true,
                'nested'   => true,
                'extra'    => [],
            ],
            'collapsedNotNestedExtra' => [
                'expanded' => false,
                'nested'   => false,
                'extra'    => ['configState' => [$this->testData['htmlId'] => true]],
            ],
            'collapsedNotNestedNoExtra' => [
                'expanded' => true,
                'nested'   => false,
                'extra'    => [],
            ],
        ];
    }
}
