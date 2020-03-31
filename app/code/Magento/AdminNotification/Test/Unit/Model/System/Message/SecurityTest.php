<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @var MockObject
     */
    protected $_cacheMock;

    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $_configMock;

    /**
     * @var MockObject
     */
    protected $_curlFactoryMock;

    /**
     * @var Security
     */
    protected $_messageModel;

    protected function setUp(): void
    {
        //Prepare objects for constructor
        $this->_cacheMock = $this->createMock(CacheInterface::class);
        $this->_scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->_curlFactoryMock = $this->createPartialMock(
            CurlFactory::class,
            ['create']
        );

        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'cache' => $this->_cacheMock,
            'scopeConfig' => $this->_scopeConfigMock,
            'curlFactory' => $this->_curlFactoryMock,
        ];
        $this->_messageModel = $objectManagerHelper->getObject(
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
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue($cached));
        $this->_cacheMock->expects($this->any())->method('save')->will($this->returnValue(null));

        $httpAdapterMock = $this->createMock(Curl::class);
        $httpAdapterMock->expects($this->any())->method('read')->will($this->returnValue($response));
        $this->_curlFactoryMock->expects($this->any())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->_scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue(null));

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
