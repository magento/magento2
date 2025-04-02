<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Fieldset\Modules;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Module\ModuleListInterface;
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
class DisableOutputTest extends TestCase
{
    /**
     * @var DisableOutput
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $elementMock;

    /**
     * @var array
     */
    protected static $elementData = [
        'htmlId'      => 'test_field_id',
        'name'        => 'test_name',
        'label'       => 'test_label',
        'elementHTML' => 'test_html',
        'legend'      => 'test_legend',
        'comment'     => 'test_comment',
        'tooltip'     => 'test_tooltip',
    ];

    /**
     * @var MockObject
     */
    protected $layoutMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $moduleListMock;

    /**
     * @var MockObject
     */
    protected $authSessionMock;

    /**
     * @var MockObject
     */
    protected $userMock;

    /**
     * @var MockObject
     */
    protected $jsHelperMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $rendererMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock->expects($this->any())
            ->method('getBlockSingleton')
            ->willReturn($rendererMock);

        $this->jsHelperMock = $this->getMockBuilder(Js::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->onlyMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn(['Test Name']);
        $this->moduleListMock->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $this->moduleListMock->expects($this->any())
            ->method('getAll')
            ->willReturn([]);
        $this->moduleListMock->expects($this->any())
            ->method('getOne')
            ->willReturn(null);

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->userMock = $this->getMockBuilder(User::class)
            ->addMethods(['getExtra'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock);

        $groupMock = $this->getMockBuilder(Group::class)
            ->onlyMethods(['getFieldsetCss'])
            ->disableOriginalConstructor()
            ->getMock();
        $groupMock->expects($this->any())->method('getFieldsetCss')->willReturn('test_fieldset_css');

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryColl = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formMock = $this->getMockBuilder(AbstractForm::class)
            ->setConstructorArgs([$factory, $factoryColl])
            ->getMock();

        $context = $this->objectManager->getObject(
            Context::class,
            [
                'layout' => $this->layoutMock,
            ]
        );

        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $js, string $selector): string {
                    return "<script>document.querySelector('$selector').$event = function () { $js };</script>";
                }
            );

        $data = [
            'context'     => $context,
            'authSession' => $this->authSessionMock,
            'jsHelper'    => $this->jsHelperMock,
            'moduleList'  => $this->moduleListMock,
            'data' => [
                'group'          => $groupMock,
                'form'           => $formMock,
            ],
            'secureRenderer' => $secureRendererMock
        ];

        $this->objectManager->prepareObjectManager();
        $this->object = $this->objectManager->getObject(
            DisableOutput::class,
            $data
        );

        $this->elementMock = $this->getMockBuilder(Text::class)
            ->addMethods(['getExpanded', 'getLegend', 'getComment', 'getTooltip', 'getIsNested'])
            ->onlyMethods(
                [
                    'getId', 'getHtmlId', 'getName', 'toHtml',
                    'addField', 'setRenderer', 'getElements'
                ]
            )
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->enableAutoload()
            ->getMock();

        $this->elementMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::$elementData['htmlId']);
        $this->elementMock->expects($this->any())
            ->method('getHtmlId')
            ->willReturn(self::$elementData['htmlId']);
        $this->elementMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::$elementData['name']);
        $this->elementMock->expects($this->any())
            ->method('getLegend')
            ->willReturn(self::$elementData['legend']);
        $this->elementMock->expects($this->any())
            ->method('getComment')
            ->willReturn(self::$elementData['comment']);
        $this->elementMock->expects($this->any())
            ->method('getTooltip')
            ->willReturn(self::$elementData['tooltip']);
        $this->elementMock->expects($this->any())
            ->method('toHtml')
            ->willReturn(self::$elementData['elementHTML']);
        $this->elementMock->expects($this->any())
            ->method('addField')
            ->willReturn($this->elementMock);
        $this->elementMock->expects($this->any())
            ->method('setRenderer')
            ->willReturn($this->elementMock);
        $this->elementMock->expects($this->any())
            ->method('getElements')
            ->willReturn([$this->elementMock]);
    }

    /**
     * @param $expanded
     * @param $nested
     * @param $extra
     * @dataProvider renderDataProvider
     */
    public function testRender($expanded, $nested, $extra)
    {
        $this->elementMock->expects($this->any())->method('getExpanded')->willReturn($expanded);
        $this->elementMock->expects($this->any())->method('getIsNested')->willReturn($nested);
        $this->userMock->expects($this->any())->method('getExtra')->willReturn($extra);
        $actualHtml = $this->object->render($this->elementMock);

        $this->assertStringContainsString(self::$elementData['htmlId'], $actualHtml);
        $this->assertStringContainsString(self::$elementData['legend'], $actualHtml);
        $this->assertStringContainsString(self::$elementData['comment'], $actualHtml);
        $this->assertStringContainsString(self::$elementData['tooltip'], $actualHtml);
        $this->assertStringContainsString(self::$elementData['elementHTML'], $actualHtml);
        if ($nested) {
            $this->assertStringContainsString('nested', $actualHtml);
        }
    }

    /**
     * @return array
     */
    public static function renderDataProvider()
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
                'extra'    => ['configState' => [self::$elementData['htmlId'] => true]],
            ],
            'collapsedNotNestedNoExtra' => [
                'expanded' => false,
                'nested'   => false,
                'extra'    => [],
            ],
        ];
    }
}
