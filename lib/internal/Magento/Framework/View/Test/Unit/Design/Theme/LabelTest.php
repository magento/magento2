<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\View\Design\Theme\Label;
use Magento\Framework\View\Design\Theme\Label\ListInterface;

class LabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Label
     */
    protected $model;

    /**
     * @var ListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelList;

    protected function setUp()
    {
        $this->labelList = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Label\ListInterface')
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
        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
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
        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
        $this->assertEquals($defaultLabel, $result[0]['label']);
        $this->assertEquals($data['label'], $result[1]['label']);
    }
}
