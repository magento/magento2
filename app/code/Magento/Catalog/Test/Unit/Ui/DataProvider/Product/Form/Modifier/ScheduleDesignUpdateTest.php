<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\ScheduleDesignUpdate;

class ScheduleDesignUpdateTest extends AbstractModifierTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(ScheduleDesignUpdate::class, [
            'arrayManager' => $this->arrayManagerMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(1);
        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }

    public function testModifyData()
    {
        $this->assertSame(['data_key' => 'data_value'], $this->getModel()->modifyData(['data_key' => 'data_value']));
    }
}
