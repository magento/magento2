<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\View\Design\Theme\Label;
use Magento\Framework\View\Design\Theme\Label\ListInterface;

class LabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Label
     */
    protected $model;

    /**
     * @var ListInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $labelList;

    protected function setUp(): void
    {
        $this->labelList = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\Label\ListInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Label(
            $this->labelList
        );
    }

    public function testToOptionArray()
    {
        $defaultLabel = (string)new \Magento\Framework\Phrase('-- No Theme --');
        $data = [
            'value' => '1',
            'label' => 'Label1',
        ];

        $this->labelList->expects($this->once())
            ->method('getLabels')
            ->willReturn([$data]);

        $result = $this->model->toOptionArray();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($defaultLabel, $result[0]['label']);
        $this->assertEquals($data['label'], $result[1]['label']);
    }

    public function testGetLabelsCollectionForSystemConfiguration()
    {
        $defaultLabel = (string)new \Magento\Framework\Phrase('-- No Theme --');
        $data = [
            'value' => '1',
            'label' => 'Label1',
        ];

        $this->labelList->expects($this->once())
            ->method('getLabels')
            ->willReturn([$data]);

        $result = $this->model->getLabelsCollectionForSystemConfiguration();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($defaultLabel, $result[0]['label']);
        $this->assertEquals($data['label'], $result[1]['label']);
    }
}
