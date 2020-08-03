<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Widget\Grid\Row;

use Magento\Backend\Model\Url;
use Magento\Backend\Model\Widget\Grid\Row\UrlGenerator;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    public function testGetUrl()
    {
        $itemId = 3;
        $urlPath = 'mng/item/edit';

        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getItemId'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())->method('getItemId')->willReturn($itemId);

        $urlModelMock = $this->createMock(Url::class);
        $urlModelMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->willReturn(
            'http://localhost/' . $urlPath . '/flag/1/item_id/' . $itemId
        );

        $model = new UrlGenerator(
            $urlModelMock,
            [
                'path' => $urlPath,
                'params' => ['flag' => 1],
                'extraParamsTemplate' => ['item_id' => 'getItemId']
            ]
        );

        $url = $model->getUrl($itemMock);

        $this->assertStringContainsString($urlPath, $url);
        $this->assertStringContainsString('flag/1', $url);
        $this->assertStringContainsString('item_id/' . $itemId, $url);
    }
}
