<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\ScheduleDesignUpdate;
use Magento\Catalog\Ui\DataProvider\Grouper;

/**
 * Class ScheduleDesignUpdateTest
 */
class ScheduleDesignUpdateTest extends AbstractModifierTest
{
    /**
     * @var Grouper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $grouper;

    protected function setUp()
    {
        parent::setUp();
        $this->grouper = $this->getMockBuilder(Grouper::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(ScheduleDesignUpdate::class, [
            'grouper' => $this->grouper,
        ]);
    }

    public function testModifyMeta()
    {
        $this->grouper->expects($this->any())
            ->method('getGroupCodeByField')
            ->willReturn('test_group_code');

        $this->assertNotEmpty($this->getModel()->modifyMeta([]));
    }

    public function testModifyData()
    {
        $this->assertSame(['data_key' => 'data_value'], $this->getModel()->modifyData(['data_key' => 'data_value']));
    }
}
