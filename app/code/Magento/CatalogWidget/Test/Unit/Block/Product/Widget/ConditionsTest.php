<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Test\Unit\Block\Product\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogWidget\Block\Product\Widget\Conditions;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Test class for \Magento\CatalogWidget\Block\Product\Widget\Conditions
 */
class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockMock;

    /**
     * @var Conditions
     */
    private $widgetConditions;

    /**
     * return void
     */
    protected function setUp()
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

        /** @var \Magento\Widget\Model\Widget\Instance|\PHPUnit_Framework_MockObject_MockObject $widgetMock */
        $widgetMock = $this->getMockBuilder(\Magento\Widget\Model\Widget\Instance::class)
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
     */
    public function testRender()
    {
        $data = ['area' => 'backend'];
        $abstractElementMock = $this->getMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getContainer'],
            [],
            '',
            false
        );
        $eventManagerMock = $this->getMock(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $scopeConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $fieldsetMock = $this->getMock(
            \Magento\Framework\Data\Form\Element\Fieldset::class,
            [],
            [],
            '',
            false
        );
        $combineMock = $this->getMock(
            \Magento\Rule\Model\Condition\Combine::class,
            [],
            [],
            '',
            false
        );
        $resolverMock = $this->getMock(
            \Magento\Framework\View\Element\Template\File\Resolver::class,
            [],
            [],
            '',
            false
        );
        $filesystemMock = $this->getMock(
            \Magento\Framework\Filesystem::class,
            ['getDirectoryRead'],
            [],
            '',
            false
        );
        $validatorMock = $this->getMock(
            \Magento\Framework\View\Element\Template\File\Validator::class,
            [],
            [],
            '',
            false
        );
        $templateEnginePoolMock = $this->getMock(
            \Magento\Framework\View\TemplateEnginePool::class,
            [],
            [],
            '',
            false
        );
        $templateEngineMock = $this->getMock(
            \Magento\Framework\View\TemplateEngineInterface::class,
            [],
            [],
            '',
            false
        );
        $directoryReadMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            [],
            '',
            false
        );

        $this->ruleMock->expects($this->once())->method('getConditions')->willReturn($combineMock);
        $combineMock->expects($this->once())->method('setJsFormObject')->willReturnSelf();
        $abstractElementMock->expects($this->any())->method('getContainer')->willReturn($fieldsetMock);
        $filesystemMock->expects($this->once())->method('getDirectoryRead')->willReturn($directoryReadMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->contextMock->expects($this->once())->method('getEnginePool')->willReturn($templateEnginePoolMock);
        $templateEnginePoolMock->expects($this->once())->method('get')->willReturn($templateEngineMock);
        $templateEngineMock->expects($this->once())->method('render')->willReturn('html');

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
