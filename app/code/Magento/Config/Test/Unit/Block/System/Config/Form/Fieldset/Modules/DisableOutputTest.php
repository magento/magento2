<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
            ->setMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleListMock->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Test Name']));
        $this->moduleListMock->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));
        $this->moduleListMock->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue([]));
        $this->moduleListMock->expects($this->any())
            ->method('getOne')
            ->will($this->returnValue(null));

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

        $groupMock = $this->getMockBuilder(Group::class)
            ->setMethods(['getFieldsetCss'])
            ->disableOriginalConstructor()
            ->getMock();
        $groupMock->expects($this->any())->method('getFieldsetCss')->will($this->returnValue('test_fieldset_css'));

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
            DisableOutput::class,
            $data
        );

        $this->elementMock = $this->getMockBuilder(Text::class)
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
            ->will($this->returnValue($this->elementData['htmlId']));
        $this->elementMock->expects($this->any())
            ->method('getHtmlId')
            ->will($this->returnValue($this->elementData['htmlId']));
        $this->elementMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->elementData['name']));
        $this->elementMock->expects($this->any())
            ->method('getLegend')
            ->will($this->returnValue($this->elementData['legend']));
        $this->elementMock->expects($this->any())
            ->method('getComment')
            ->will($this->returnValue($this->elementData['comment']));
        $this->elementMock->expects($this->any())
            ->method('getTooltip')
            ->will($this->returnValue($this->elementData['tooltip']));
        $this->elementMock->expects($this->any())
            ->method('toHtml')
            ->will($this->returnValue($this->elementData['elementHTML']));
        $this->elementMock->expects($this->any())
            ->method('addField')
            ->will($this->returnValue($this->elementMock));
        $this->elementMock->expects($this->any())
            ->method('setRenderer')
            ->will($this->returnValue($this->elementMock));
        $this->elementMock->expects($this->any())
            ->method('getElements')
            ->will($this->returnValue([$this->elementMock]));
    }

    /**
     * @param $expanded
     * @param $nested
     * @param $extra
     * @dataProvider renderDataProvider
     */
    public function testRender($expanded, $nested, $extra)
    {
        $this->elementMock->expects($this->any())->method('getExpanded')->will($this->returnValue($expanded));
        $this->elementMock->expects($this->any())->method('getIsNested')->will($this->returnValue($nested));
        $this->userMock->expects($this->any())->method('getExtra')->willReturn($extra);
        $actualHtml = $this->object->render($this->elementMock);

        $this->assertContains($this->elementData['htmlId'], $actualHtml);
        $this->assertContains($this->elementData['legend'], $actualHtml);
        $this->assertContains($this->elementData['comment'], $actualHtml);
        $this->assertContains($this->elementData['tooltip'], $actualHtml);
        $this->assertContains($this->elementData['elementHTML'], $actualHtml);
        if ($nested) {
            $this->assertContains('nested', $actualHtml);
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
