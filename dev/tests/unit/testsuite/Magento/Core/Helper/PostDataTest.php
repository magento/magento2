<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

use Magento\Framework\App\Action\Action;

class PostDataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPostData()
    {
        $url = '/controller/sample/action/url/';
        $product = ['product' => new \Magento\Framework\Object(['id' => 1])];
        $expected = json_encode([
            'action' => $url,
            'data' => [
                'product' => new \Magento\Framework\Object(['id' => 1]),
                Action::PARAM_NAME_URL_ENCODED => strtr(base64_encode($url . 'for_uenc'), '+/=', '-_,'),
            ],
        ]);

        $contextMock = $this->getMock(
            'Magento\Framework\App\Helper\Context',
            ['getUrlBuilder', 'getUrlEncoder'],
            [],
            '',
            false
        );
        $urlBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
            [],
            '',
            true,
            true,
            true,
            ['getCurrentUrl']
        );

        $encoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')->getMock();
        $encoder->expects($this->once())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });
        $contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->once())
            ->method('getUrlEncoder')
            ->willReturn($encoder);
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url . 'for_uenc'));

        $model = new \Magento\Core\Helper\PostData($contextMock);

        $actual = $model->getPostData($url, $product);
        $this->assertEquals($expected, $actual);
    }
}
