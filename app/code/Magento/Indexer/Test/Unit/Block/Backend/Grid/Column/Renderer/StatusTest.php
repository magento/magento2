<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $indexValues
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender($indexValues, $expectedResult)
    {
        $context = $this->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $model = new \Magento\Indexer\Block\Backend\Grid\Column\Renderer\Status($context);
        $obj = new \Magento\Framework\DataObject();
        $obj->setGetter(null);
        $obj->setDefault('');
        $obj->setValue('');
        $obj->setIndex($indexValues[0]);
        $obj->setData($indexValues[0], $indexValues[0]);
        $model->setColumn($obj);
        $model->setIndex($indexValues[0]);
        $result = $model->render($obj);
        $this->assertEquals(
            $result,
            '<span class="' . $expectedResult['class'] . '"><span>' . $expectedResult['text'] . '</span></span>'
        );
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            'set1' => [
                [\Magento\Framework\Indexer\StateInterface::STATUS_INVALID],
                ['class' => 'grid-severity-critical', 'text' => 'Reindex required']
            ],
            'set2' => [
                [\Magento\Framework\Indexer\StateInterface::STATUS_VALID],
                ['class' => 'grid-severity-notice', 'text' => 'Ready']
            ],
            'set3' => [
                [\Magento\Framework\Indexer\StateInterface::STATUS_WORKING],
                ['class' => 'grid-severity-major', 'text' => 'Processing']
            ]
        ];
    }
}
