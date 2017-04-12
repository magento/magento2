<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\TestFramework\Helper\Bootstrap;
use Zend\Stdlib\RequestInterface as Request;
use Zend\View\Model\JsonModel;

class UrlCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlCheck
     */
    private $controller;

    protected function setUp()
    {
        $this->controller = Bootstrap::getObjectManager()->create(UrlCheck::class);
    }

    /**
     * @param array $requestContent
     * @param bool $successUrl
     * @param bool $successSecureUrl
     * @return void
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($requestContent, $successUrl, $successSecureUrl)
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($requestContent));

        $requestProperty = new \ReflectionProperty(get_class($this->controller), 'request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $requestMock);

        $resultModel = new JsonModel(['successUrl' => $successUrl, 'successSecureUrl' => $successSecureUrl]);

        $this->assertEquals($resultModel, $this->controller->indexAction());
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return [
            [
                'requestContent' => [
                    'address' => [
                        'actual_base_url' => 'http://example.com/'
                    ],
                    'https' => [
                        'text' => 'https://example.com/',
                        'admin' => true,
                        'front' => false
                    ],
                ],
                'successUrl' => true,
                'successSecureUrl' => true
            ],
            [
                'requestContent' => [
                    'address' => [
                        'actual_base_url' => 'http://example.com/folder/'
                    ],
                    'https' => [
                        'text' => 'https://example.com/folder_name/',
                        'admin' => false,
                        'front' => true
                    ],
                ],
                'successUrl' => true,
                'successSecureUrl' => true
            ],
            [
                'requestContent' => [
                    'address' => [
                        'actual_base_url' => 'ftp://example.com/'
                    ],
                    'https' => [
                        'text' => 'https://example.com_test/',
                        'admin' => true,
                        'front' => true
                    ],
                ],
                'successUrl' => false,
                'successSecureUrl' => false
            ],
        ];
    }
}
