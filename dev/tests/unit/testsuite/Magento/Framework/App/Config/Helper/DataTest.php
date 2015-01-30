<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param array $allowedIps
     * @param bool $expected
     * @dataProvider isDevAllowedDataProvider
     */
    public function testIsDevAllowed($allowedIps, $expected, $callNum = 1)
    {
        $storeId = 'storeId';

        $scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEV_ALLOW_IPS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($allowedIps));

        $remoteAddressMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress')
            ->disableOriginalConstructor()
            ->getMock();
        $remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue('remoteAddress'));

        $httpHeaderMock = $this->getMockBuilder('Magento\Framework\HTTP\Header')
            ->disableOriginalConstructor()
            ->getMock();
        $httpHeaderMock->expects($this->exactly($callNum))
            ->method('getHttpHost')
            ->will($this->returnValue('httpHost'));

        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'remoteAddress' => $remoteAddressMock,
                'httpHeader' => $httpHeaderMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'scopeConfig' => $scopeConfigMock,
                'context' => $context,
            ]
        );
        $this->assertEquals($expected, $helper->isDevAllowed($storeId));
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

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject('Magento\Framework\App\Config\Helper\Data', $arguments);
    }
}
