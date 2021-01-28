<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

class SecurityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_curlFactoryMock;

    /**
     * @var \Magento\AdminNotification\Model\System\Message\Security
     */
    protected $_messageModel;

    protected function setUp(): void
    {
        //Prepare objects for constructor
        $this->_cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_curlFactoryMock = $this->createPartialMock(
            \Magento\Framework\HTTP\Adapter\CurlFactory::class,
            ['create']
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'cache' => $this->_cacheMock,
            'scopeConfig' => $this->_scopeConfigMock,
            'curlFactory' => $this->_curlFactoryMock,
        ];
        $this->_messageModel = $objectManagerHelper->getObject(
            \Magento\AdminNotification\Model\System\Message\Security::class,
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
        $this->_cacheMock->expects($this->any())->method('load')->willReturn($cached);
        $this->_cacheMock->expects($this->any())->method('save')->willReturn(null);

        $httpAdapterMock = $this->createMock(\Magento\Framework\HTTP\Adapter\Curl::class);
        $httpAdapterMock->expects($this->any())->method('read')->willReturn($response);
        $this->_curlFactoryMock->expects($this->any())->method('create')->willReturn($httpAdapterMock);

        $this->_scopeConfigMock->expects($this->any())->method('getValue')->willReturn(null);

        $this->assertEquals($expectedResult, $this->_messageModel->isDisplayed());
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

        $this->assertStringStartsWith($messageStart, (string)$this->_messageModel->getText());
    }
}
