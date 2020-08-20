<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

use Magento\AdminNotification\Model\System\Message\Security;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CurlFactory|MockObject
     */
    private $curlFactoryMock;

    /**
     * @var Security
     */
    private $messageModel;

    protected function setUp(): void
    {
        //Prepare objects for constructor
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->curlFactoryMock = $this->createPartialMock(
            CurlFactory::class,
            ['create']
        );

        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'cache' => $this->cacheMock,
            'scopeConfig' => $this->scopeConfigMock,
            'curlFactory' => $this->curlFactoryMock,
        ];
        $this->messageModel = $objectManagerHelper->getObject(
            Security::class,
            $arguments
        );
    }

    /**
     *
     * @param $expectedResult
     * @param $cached
     * @param $response
     * @return void
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expectedResult, $cached, $response)
    {
        $this->cacheMock->method('load')->willReturn($cached);
        $this->cacheMock->method('save')->willReturn(null);

        $httpAdapterMock = $this->createMock(Curl::class);
        $httpAdapterMock->method('read')->willReturn($response);
        $this->curlFactoryMock->method('create')->willReturn($httpAdapterMock);

        $this->scopeConfigMock->method('getValue')->willReturn(null);

        $this->assertEquals($expectedResult, $this->messageModel->isDisplayed());
    }

    /**
     * @return array
     */
    public function isDisplayedDataProvider()
    {
        return [
            'cached_case' => [false, true, ''],
            'accessible_file' => [true, false, 'HTTP/1.1 200'],
            'inaccessible_file' => [false, false, 'HTTP/1.1 403']
        ];
    }

    public function testGetText()
    {
        $messageStart = 'Your web server is set up incorrectly';

        $this->assertStringStartsWith($messageStart, (string)$this->messageModel->getText());
    }
}
