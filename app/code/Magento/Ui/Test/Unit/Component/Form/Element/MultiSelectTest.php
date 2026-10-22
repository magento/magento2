<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * @method MultiSelect getModel
 */
class MultiSelectTest extends AbstractElementTestCase
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return MultiSelect::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName(): void
    {
        $this->contextMock->expects($this->never())->method('getProcessor');

        $this->assertSame(MultiSelect::NAME, $this->getModel()->getComponentName());
    }

    public function testPrepare(): void
    {
        $processorMock = $this->createPartialMock(Processor::class, ['register', 'notify']);
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $this->getModel()->prepare();

        $this->assertNotEmpty($this->getModel()->getData());
    }

    public function testPrepareSetsDefaultSizeWhenNotConfigured(): void
    {
        $model = $this->createPreparedModel(['config' => ['label' => 'Sort by']]);

        $config = $model->getData('config');
        $this->assertSame(MultiSelect::DEFAULT_SIZE, $config['size']);
        $this->assertSame('Sort by', $config['label']);
    }

    public function testPrepareKeepsConfiguredSize(): void
    {
        $model = $this->createPreparedModel(['config' => ['size' => 12]]);

        $this->assertSame(12, $model->getData('config')['size']);
    }

    private function createPreparedModel(array $data): MultiSelect
    {
        $processorMock = $this->createPartialMock(Processor::class, ['register', 'notify']);
        $this->contextMock->method('getProcessor')->willReturn($processorMock);
        $model = $this->objectManager->getObject(
            MultiSelect::class,
            ['context' => $this->contextMock, 'data' => $data]
        );
        $model->prepare();

        return $model;
    }
}
