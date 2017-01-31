<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Developer\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteAddressMock;

    /**
     * @var \Magento\Framework\HTTP\Header | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpHeaderMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Magento\Developer\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
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
                \Magento\Developer\Helper\Data::XML_PATH_DEV_ALLOW_IPS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($allowedIps));

        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue('remoteAddress'));

        $this->httpHeaderMock->expects($this->exactly($callNum))
            ->method('getHttpHost')
            ->will($this->returnValue('httpHost'));

        $this->assertEquals($expected, $this->helper->isDevAllowed($storeId));
    }

    public function isDevAllowedDataProvider()
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
