<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\AbstractElement;
use Magento\Framework\View\Element\UiComponent\Processor;

/**
 * Class AbstractElementTest
 */
abstract class AbstractElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AbstractElement
     */
    protected $model;

    /**
     * @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $processorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)->getMockForAbstractClass();
    }

    /**
     * @return string
     */
    abstract protected function getModelName();

    /**
     * @return mixed
     */
    abstract public function testGetComponentName();

    /**
     * @return AbstractElement
     */
    protected function getModel()
    {
        if (null === $this->model) {
            $this->model = $this->objectManager->getObject($this->getModelName(), [
                'context' => $this->contextMock,
            ]);
        }

        return $this->model;
    }

    public function testGetHtmlId()
    {
        $this->assertEquals('', $this->getModel()->getHtmlId());
    }

    public function testGetValue()
    {
        $this->assertNull($this->getModel()->getValue());
    }

    public function testGetFormInputName()
    {
        $this->assertNull($this->getModel()->getFormInputName());
    }

    public function testIsReadonly()
    {
        $this->assertFalse($this->getModel()->isReadonly());
    }

    public function testGetCssClasses()
    {
        $this->assertNull($this->getModel()->getCssClasses());
    }
}
