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
use PHPUnit\Framework\TestCase;

/**
 * Class SecurityTest
 *
 * @package Magento\AdminNotification\Test\Unit\Model\System\Message
 */
class SecurityTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $curlFactoryMock;

    /**
     * @var Security
     */
    protected $messageModel;

    protected function setUp()
    {
        //Prepare objects for constructor
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
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
     * @dataProvider isDisplayedDataProvider
     * @param bool $expectedResult
     * @param bool $cached
     * @param string $response
     * @return void
     */
    public function testIsDisplayed($expectedResult, $cached, $response)
    {
        $this->cacheMock->expects(static::any())->method('load')->will(static::returnValue($cached));
        $this->cacheMock->expects(static::any())->method('save')->will(static::returnValue(null));

        $httpAdapterMock = $this->createMock(Curl::class);
        $httpAdapterMock->expects(static::any())->method('read')->will(static::returnValue($response));
        $this->curlFactoryMock->expects(static::any())->method('create')->will(static::returnValue($httpAdapterMock));

        $this->scopeConfigMock->expects(static::any())->method('getValue')->will(static::returnValue(null));

        static::assertEquals($expectedResult, $this->messageModel->isDisplayed());
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

        static::assertStringStartsWith($messageStart, (string)$this->messageModel->getText());
    }
}
