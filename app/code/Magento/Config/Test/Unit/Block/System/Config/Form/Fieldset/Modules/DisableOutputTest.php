<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Fieldset\Modules;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DisableOutputTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $elementMock;

    /**
     * @var array
     */
    protected $elementData = [
        'htmlId'      => 'test_field_id',
        'name'        => 'test_name',
        'label'       => 'test_label',
        'elementHTML' => 'test_html',
        'legend'      => 'test_legend',
        'comment'     => 'test_comment',
        'tooltip'     => 'test_tooltip',
    ];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $authSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $userMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsHelperMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $rendererMock = $this->getMockBuilder(\Magento\Config\Block\System\Config\Form\Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock->expects($this->any())
            ->method('getBlockSingleton')
            ->willReturn($rendererMock);

        $this->jsHelperMock = $this->getMockBuilder(\Magento\Framework\View\Helper\Js::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleListMock = $this->getMockBuilder(\Magento\Framework\Module\ModuleListInterface::class)
            ->setMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->authSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->setMethods(['getExtra'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock);

        $groupMock = $this->getMockBuilder(\Magento\Config\Model\Config\Structure\Element\Group::class)
            ->setMethods(['getFieldsetCss'])
            ->disableOriginalConstructor()
            ->getMock();
        $groupMock->expects($this->any())->method('getFieldsetCss')->willReturn('test_fieldset_css');

        $factory = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryColl = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formMock = $this->getMockBuilder(\Magento\Framework\Data\Form\AbstractForm::class)
            ->setConstructorArgs([$factory, $factoryColl])
            ->getMock();

        $context = $this->objectManager->getObject(
            \Magento\Backend\Block\Context::class,
            [
                'layout' => $this->layoutMock,
            ]
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
        ];

        $this->object = $this->objectManager->getObject(
            \Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput::class,
            $data
        );

        $this->elementMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Text::class)
            ->setMethods(
                [
                    'getId', 'getHtmlId', 'getName', 'getExpanded', 'getLegend', 'getComment', 'getTooltip', 'toHtml',
                    'addField', 'setRenderer', 'getElements', 'getIsNested'
                ]
            )
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->enableAutoload()
            ->getMock();

        $this->elementMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->elementData['htmlId']);
        $this->elementMock->expects($this->any())
            ->method('getHtmlId')
            ->willReturn($this->elementData['htmlId']);
        $this->elementMock->expects($this->any())
            ->method('getName')
            ->willReturn($this->elementData['name']);
        $this->elementMock->expects($this->any())
            ->method('getLegend')
            ->willReturn($this->elementData['legend']);
        $this->elementMock->expects($this->any())
            ->method('getComment')
            ->willReturn($this->elementData['comment']);
        $this->elementMock->expects($this->any())
            ->method('getTooltip')
            ->willReturn($this->elementData['tooltip']);
        $this->elementMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($this->elementData['elementHTML']);
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

        $this->assertStringContainsString($this->elementData['htmlId'], $actualHtml);
        $this->assertStringContainsString($this->elementData['legend'], $actualHtml);
        $this->assertStringContainsString($this->elementData['comment'], $actualHtml);
        $this->assertStringContainsString($this->elementData['tooltip'], $actualHtml);
        $this->assertStringContainsString($this->elementData['elementHTML'], $actualHtml);
        if ($nested) {
            $this->assertStringContainsString('nested', $actualHtml);
        }
    }

    /**
     * @return array
     */
    public function renderDataProvider()
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
                'extra'    => ['configState' => [$this->elementData['htmlId'] => true]],
            ],
            'collapsedNotNestedNoExtra' => [
                'expanded' => false,
                'nested'   => false,
                'extra'    => [],
            ],
        ];
    }
}
