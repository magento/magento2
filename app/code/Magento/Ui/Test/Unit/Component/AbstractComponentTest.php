<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\AbstractComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractComponentTest extends TestCase
{
    /**
     * @var AbstractComponent
     */
    protected $abstractComponent;

    /**
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->contextMock->expects($this->never())->method('getProcessor');
        $this->abstractComponent = $this->getMockBuilder(AbstractComponent::class)
            ->enableOriginalConstructor()
            ->setMethods(['getComponentName'])
            ->setConstructorArgs(['context' => $this->contextMock])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testGetContext()
    {
        $this->assertSame($this->contextMock, $this->abstractComponent->getContext());
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $name = 'some name';
        $this->abstractComponent->setData('name', $name);
        $this->assertEquals($name, $this->abstractComponent->getName());
    }

    /**
     * @param string $renderResult
     * @return void
     */
    protected function initTestRender($renderResult)
    {
        $template = 'template';
        $this->abstractComponent->setData('template', $template);

        /** @var ContentTypeInterface|MockObject $renderEngineMock */
        $renderEngineMock = $this->getMockForAbstractClass(ContentTypeInterface::class);
        $renderEngineMock->expects($this->once())
            ->method('render')
            ->with($this->abstractComponent, $template . '.xhtml')
            ->willReturn($renderResult);

        $this->contextMock->expects($this->once())
            ->method('getRenderEngine')
            ->willReturn($renderEngineMock);
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $renderResult = 'some html code';
        $this->initTestRender($renderResult);
        $this->assertEquals($renderResult, $this->abstractComponent->render());
    }

    /**
     * @return void
     */
    public function testGetComponentNotExists()
    {
        $this->assertNull($this->abstractComponent->getComponent('nameComponent'));
    }

    /**
     * @return void
     */
    public function testGetChildComponentsEmptyArray()
    {
        $this->assertEquals([], $this->abstractComponent->getChildComponents());
    }

    /**
     * @return void
     */
    public function testAddGetChildComponents()
    {
        /** @var UiComponentInterface|MockObject $uiComponentMock */
        $uiComponentMock = $this->getMockForAbstractClass(UiComponentInterface::class);
        $name = 'componentName';

        $this->abstractComponent->addComponent($name, $uiComponentMock);
        $this->assertEquals($uiComponentMock, $this->abstractComponent->getComponent($name));
    }

    /**
     * @return void
     */
    public function testGetChildComponents()
    {
        /** @var UiComponentInterface|MockObject $uiComponentMock */
        $uiComponentMock = $this->getMockForAbstractClass(UiComponentInterface::class);
        $name = 'componentName';
        $expectedResult = [$name => $uiComponentMock];

        $this->abstractComponent->addComponent($name, $uiComponentMock);
        $this->assertEquals($expectedResult, $this->abstractComponent->getChildComponents());
    }

    /**
     * @return void
     */
    public function testRenderChildComponentNotExists()
    {
        $this->assertNull($this->abstractComponent->renderChildComponent('someComponent'));
    }

    /**
     * @return void
     */
    public function testRenderChildComponent()
    {
        $name = 'componentName';
        $expectedResult = 'some html code';
        /** @var UiComponentInterface|MockObject $uiComponentMock */
        $uiComponentMock = $this->getMockForAbstractClass(UiComponentInterface::class);
        $uiComponentMock->expects($this->once())
            ->method('render')
            ->willReturn($expectedResult);

        $this->abstractComponent->addComponent($name, $uiComponentMock);
        $this->assertEquals($expectedResult, $this->abstractComponent->renderChildComponent($name));
    }

    /**
     * @return void
     */
    public function testGetTemplate()
    {
        $template = 'sample';
        $this->abstractComponent->setData('template', $template);

        $this->assertEquals($template . '.xhtml', $this->abstractComponent->getTemplate());
    }

    /**
     * @param mixed $config
     * @param array $expectedResult
     * @return void
     * @dataProvider getConfigurationDataProvider
     */
    public function testGetConfiguration($config, array $expectedResult)
    {
        $this->abstractComponent->setData('config', $config);
        $this->assertSame($expectedResult, $this->abstractComponent->getConfiguration());
    }

    /**
     * @return array
     */
    public function getConfigurationDataProvider()
    {
        return [
            ['config' => null, 'expectedResult' => []],
            ['config' => [], 'expectedResult' => []],
            ['config' => ['visible' => true], 'expectedResult' => ['visible' => true]],
        ];
    }

    /**
     * @param array $jsConfig
     * @param array $expectedResult
     * @return void
     * @dataProvider getJsConfigDataProvider
     */
    public function testGetJsConfig(array $jsConfig, array $expectedResult)
    {
        $namespace = 'my_namespace';
        /** @var UiComponentInterface|MockObject $uiComponentMock */
        $uiComponentMock = $this->getMockBuilder(UiComponentInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $uiComponentMock->expects($this->once())
            ->method('getData')
            ->with('js_config')
            ->willReturnOnConsecutiveCalls($jsConfig);
        $uiComponentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);
        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn($namespace);

        $this->assertEquals($expectedResult, $this->abstractComponent->getJsConfig($uiComponentMock));
    }

    /**
     * @return array
     */
    public function getJsConfigDataProvider()
    {
        return [
            [
                'jsConfig' => [],
                'expectedResult' => ['extends' => 'my_namespace']
            ],
            [
                'jsConfig' => ['name' => 'test'],
                'expectedResult' => ['name' => 'test', 'extends' => 'my_namespace']
            ],
            [
                'jsConfig' => ['name' => 'test', 'extends' => 'some_extends'],
                'expectedResult' => ['name' => 'test', 'extends' => 'some_extends']
            ],
        ];
    }
}
