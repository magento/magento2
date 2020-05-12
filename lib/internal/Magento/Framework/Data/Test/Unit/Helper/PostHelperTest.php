<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Url\Helper\Data;
use PHPUnit\Framework\TestCase;

class PostHelperTest extends TestCase
{
    public function testGetPostData()
    {
        $url = '/controller/sample/action/url/';
        $product = ['product' => new DataObject(['id' => 1])];
        $expected = json_encode([
            'action' => $url,
            'data' => [
                'product' => new DataObject(['id' => 1]),
                Action::PARAM_NAME_URL_ENCODED => strtr(base64_encode($url . 'for_uenc'), '+/=', '-_,'),
            ],
        ]);

        $contextMock =
            $this->createPartialMock(Context::class, ['getUrlBuilder', 'getUrlEncoder']);
        $urlHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlHelper->expects($this->once())
            ->method('getEncodedUrl')
            ->willReturn('L2NvbnRyb2xsZXIvc2FtcGxlL2FjdGlvbi91cmwvZm9yX3VlbmM,');

        $model = new PostHelper($contextMock, $urlHelper);

        $actual = $model->getPostData($url, $product);
        $this->assertEquals($expected, $actual);
    }
}
