<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\Phrase;
use Magento\Framework\View\Design\Theme\Label;
use Magento\Framework\View\Design\Theme\Label\ListInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    /**
     * @var Label
     */
    protected $model;

    /**
     * @var ListInterface|MockObject
     */
    protected $labelList;

    protected function setUp(): void
    {
        $this->labelList = $this->getMockBuilder(ListInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Label(
            $this->labelList
        );
    }

    public function testToOptionArray()
    {
        $defaultLabel = (string)new Phrase('-- No Theme --');
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
        $defaultLabel = (string)new Phrase('-- No Theme --');
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
