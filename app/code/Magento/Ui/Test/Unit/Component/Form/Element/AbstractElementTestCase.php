<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\AbstractElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractElementTestCase extends TestCase
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
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var Processor|MockObject
     */
    protected $processorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
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
