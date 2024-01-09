<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Block\Product\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogWidget\Block\Product\Widget\Conditions;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Element\Template\File\Validator;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Rule\Model\Condition\Combine;
use Magento\Widget\Model\Widget\Instance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CatalogWidget\Block\Product\Widget\Conditions
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConditionsTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Rule|MockObject
     */
    protected $ruleMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var BlockInterface|MockObject
     */
    private $blockMock;

    /**
     * @var Conditions
     */
    private $widgetConditions;

    /**
     * return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->blockMock = $this->getMockBuilder(BlockInterface::class)
            ->setMethods(['getWidgetValues'])
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
    }

    /**
     * @return void
     */
    public function testConstructWithEmptyData()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_widget_instance')
            ->willReturn(null);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('wysiwyg_widget.options')
            ->willReturn(null);
        $this->blockMock->expects($this->never())
            ->method('getWidgetValues');
        $this->ruleMock->expects($this->never())
            ->method('loadPost');

        $this->objectManagerHelper->getObject(
            Conditions::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'rule' => $this->ruleMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testConstructWithWidgetInstance()
    {
        $widgetParams = ['conditions' => 'some conditions'];

        /** @var Instance|MockObject $widgetMock */
        $widgetMock = $this->getMockBuilder(Instance::class)
            ->disableOriginalConstructor()
            ->getMock();
        $widgetMock->expects($this->once())
            ->method('getWidgetParameters')
            ->willReturn($widgetParams);

        $this->layoutMock->expects($this->never())
            ->method('getBlock');
        $this->blockMock->expects($this->never())
            ->method('getWidgetValues');
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_widget_instance')
            ->willReturn($widgetMock);
        $this->ruleMock->expects($this->once())
            ->method('loadPost')
            ->with($widgetParams)
            ->willReturnSelf();

        $this->objectManagerHelper->getObject(
            Conditions::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'rule' => $this->ruleMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testConstructWithParamsFromBlock()
    {
        $widgetParams = ['conditions' => 'some conditions'];

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_widget_instance')
            ->willReturn(null);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('wysiwyg_widget.options')
            ->willReturn($this->blockMock);
        $this->blockMock->expects($this->once())
            ->method('getWidgetValues')
            ->willReturn($widgetParams);
        $this->ruleMock->expects($this->once())
            ->method('loadPost')
            ->with($widgetParams)
            ->willReturnSelf();

        $this->objectManagerHelper->getObject(
            Conditions::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'rule' => $this->ruleMock,
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRender()
    {
        $data = ['area' => 'backend'];
        $abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['getContainer'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $fieldsetMock = $this->createMock(Fieldset::class);
        $combineMock = $this->createMock(Combine::class);
        $resolverMock = $this->createMock(Resolver::class);
        $filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);
        $validatorMock = $this->createMock(Validator::class);
        $templateEnginePoolMock = $this->createMock(TemplateEnginePool::class);
        $templateEngineMock = $this->getMockForAbstractClass(TemplateEngineInterface::class);
        $directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);

        $this->ruleMock->expects($this->once())->method('getConditions')->willReturn($combineMock);
        $combineMock->expects($this->once())->method('setJsFormObject')->willReturnSelf();
        $abstractElementMock->expects($this->any())->method('getContainer')->willReturn($fieldsetMock);
        $filesystemMock->expects($this->once())->method('getDirectoryRead')->willReturn($directoryReadMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->contextMock->expects($this->once())->method('getEnginePool')->willReturn($templateEnginePoolMock);
        $templateEnginePoolMock->expects($this->once())->method('get')->willReturn($templateEngineMock);
        $templateEngineMock->expects($this->once())->method('render')->willReturn('html');
        $resolverMock->method('getTemplateFileName')->willReturn('');

        $this->widgetConditions = $this->objectManagerHelper->getObject(
            Conditions::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'rule' => $this->ruleMock,
                '_eventManager' => $eventManagerMock,
                '_filesystem' => $filesystemMock,
                '_scopeConfig' => $scopeConfigMock,
                'validator' => $validatorMock,
                'resolver' => $resolverMock,
                'data' => $data
            ]
        );

        $this->assertEquals($this->widgetConditions->render($abstractElementMock), 'html');
    }
}
