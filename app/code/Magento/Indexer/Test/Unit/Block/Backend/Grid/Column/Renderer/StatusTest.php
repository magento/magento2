<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Block\Backend\Grid\Column\Renderer\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @param array $indexValues
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender($indexValues, $expectedResult)
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model = new Status($context);
        $obj = new DataObject();
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
                [StateInterface::STATUS_INVALID],
                ['class' => 'grid-severity-critical', 'text' => 'Reindex required']
            ],
            'set2' => [
                [StateInterface::STATUS_VALID],
                ['class' => 'grid-severity-notice', 'text' => 'Ready']
            ],
            'set3' => [
                [StateInterface::STATUS_WORKING],
                ['class' => 'grid-severity-minor', 'text' => 'Processing']
            ]
        ];
    }
}
