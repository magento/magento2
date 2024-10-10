<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Helper;

use Magento\Developer\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Header;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var RemoteAddress|MockObject
     */
    protected $remoteAddressMock;

    /**
     * @var Header|MockObject
     */
    protected $httpHeaderMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $context = $arguments['context'];
        $this->scopeConfigMock = $context->getScopeConfig();
        $this->remoteAddressMock = $context->getRemoteAddress();
        $this->httpHeaderMock = $context->getHttpHeader();
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @param array $allowedIps
     * @param bool $expected
     * @dataProvider isDevAllowedDataProvider
     */
    public function testIsDevAllowed($allowedIps, $expected, $callNum = 1)
    {
        $storeId = 'storeId';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEV_ALLOW_IPS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($allowedIps);

        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->willReturn('remoteAddress');

        $this->httpHeaderMock->expects($this->exactly($callNum))
            ->method('getHttpHost')
            ->willReturn('httpHost');

        $this->assertEquals($expected, $this->helper->isDevAllowed($storeId));
    }

    /**
     * @return array
     */
    public static function isDevAllowedDataProvider()
    {
        return [
            'allow_nothing' => [
                '',
                true,
                0,
            ],
            'allow_remote_address' => [
                'ip1, ip2, remoteAddress',
                true,
                0,
            ],
            'allow_http_host' => [
                'ip1, ip2, httpHost',
                true,
            ],
            'allow_neither' => [
                'ip1, ip2, ip3',
                false,
            ],
        ];
    }
}
