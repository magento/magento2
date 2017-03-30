<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Helper;

use Magento\Framework\App\Action\Action;

class PostHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPostData()
    {
        $url = '/controller/sample/action/url/';
        $product = ['product' => new \Magento\Framework\DataObject(['id' => 1])];
        $expected = json_encode([
            'action' => $url,
            'data' => [
                'product' => new \Magento\Framework\DataObject(['id' => 1]),
                Action::PARAM_NAME_URL_ENCODED => strtr(base64_encode($url . 'for_uenc'), '+/=', '-_,'),
            ],
        ]);

        $contextMock = $this->getMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getUrlBuilder', 'getUrlEncoder'],
            [],
            '',
            false
        );
        $urlHelper = $this->getMockBuilder(\Magento\Framework\Url\Helper\Data::class)
            ->disableOriginalConstructor()->getMock();
        $urlHelper->expects($this->once())
            ->method('getEncodedUrl')
            ->willReturn('L2NvbnRyb2xsZXIvc2FtcGxlL2FjdGlvbi91cmwvZm9yX3VlbmM,');

        $model = new \Magento\Framework\Data\Helper\PostHelper($contextMock, $urlHelper);

        $actual = $model->getPostData($url, $product);
        $this->assertEquals($expected, $actual);
    }
}
