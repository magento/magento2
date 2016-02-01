<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Helper;

/**
 * Test class for \Magento\Security\Helper\SecurityConfig testing
 */
class SecurityConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $helper;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock =  $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );

        $this->remoteAddressMock =  $this->getMock(
            '\Magento\Framework\HTTP\PhpEnvironment\RemoteAddress',
            ['getRemoteAddress'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManager->getObject(
            'Magento\Security\Helper\SecurityConfig',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * Test get time period to calculate limitations
     * @return void
     */
    public function testGetTimePeriodToCalculateLimitations()
    {
        $this->assertEquals(
            \Magento\Security\Helper\SecurityConfig::TIME_PERIOD_TO_CALCULATE_LIMITATIONS,
            $this->helper->getTimePeriodToCalculateLimitations()
        );
    }

    /**
     * Test get customer service email
     * @return void
     */
    public function testGetCustomerServiceEmail()
    {
        $email = 'test@example.com';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Security\Helper\SecurityConfig::XML_PATH_EMAIL_RECIPIENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will(
                $this->returnValue($email)
            );
        $this->assertEquals($email, $this->helper->getCustomerServiceEmail());
    }

    /**
     * Test get admin session lifetime
     * @return void
     */
    public function testGetAdminSessionLifetime()
    {
        $lifetime = 10;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME)
            ->will(
                $this->returnValue($lifetime)
            );
        $this->assertEquals($lifetime, $this->helper->getAdminSessionLifetime());
    }

    /**
     * @param bool $isShared
     * @dataProvider dataProviderBoolValues
     */
    public function testIsAdminAccountSharingIsEnabled($isShared)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(\Magento\Security\Helper\SecurityConfig::XML_PATH_ADMIN_ACCOUNT_SHARING)
            ->will(
                $this->returnValue($isShared)
            );
        $this->assertEquals($isShared, $this->helper->isAdminAccountSharingEnabled());
    }

    /**
     * @param bool $ipToLong
     * @dataProvider dataProviderBoolValues
     */
    public function testGetRemoteIp($ipToLong)
    {
        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->will(
                $this->returnValue($ipToLong)
            );

        $this->assertEquals($ipToLong, $this->helper->getRemoteIp($ipToLong));
    }

    /**
     * @return array
     */
    public function dataProviderBoolValues()
    {
        return [[true], [false]];
    }

    /**
     * @param int $resetMethod
     * @param int $scope
     * @dataProvider dataProviderResetMethodValues
     */
    public function testGetLimitPasswordResetRequestsMethod($resetMethod, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathByScope($scope)
                . \Magento\Security\Helper\SecurityConfig::XML_PATH_LIMIT_PASSWORD_RESET_REQUESTS_METHOD
            )
            ->will(
                $this->returnValue($resetMethod)
            );
        $this->assertEquals($resetMethod, $this->helper->getLimitPasswordResetRequestsMethod($scope));
    }

    /**
     * @return array
     */
    public function dataProviderResetMethodValues()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Security\Model\Config\Source\ResetMethod'
        );

        $optionKeys = array_keys($model->toArray());
        $data = [];
        foreach ($optionKeys as $key) {
            $data[] = [$key, \Magento\Security\Helper\SecurityConfig::FRONTED_AREA_SCOPE];
            $data[] = [$key, \Magento\Security\Helper\SecurityConfig::ADMIN_AREA_SCOPE];
        }

        return $data;
    }

    /**
     * Get xml path by scope
     *
     * @param int $scope
     * @return string
     */
    protected function getXmlPathByScope($scope)
    {
        if ($scope == \Magento\Security\Helper\SecurityConfig::FRONTED_AREA_SCOPE) {
            return \Magento\Security\Helper\SecurityConfig::XML_PATH_FRONTED_AREA;
        } elseif ($scope == \Magento\Security\Helper\SecurityConfig::ADMIN_AREA_SCOPE) {
            return \Magento\Security\Helper\SecurityConfig::XML_PATH_ADMIN_AREA;
        }
    }


    /**
     * @param int $limitNumber
     * @param int $scope
     * @dataProvider dataProviderNumberValueWithScope
     */
    public function testGetLimitNumberPasswordResetRequests($limitNumber, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathByScope($scope)
                . \Magento\Security\Helper\SecurityConfig::XML_PATH_LIMIT_NUMBER_REQUESTS
            )
            ->will(
                $this->returnValue($limitNumber)
            );
        $this->assertEquals($limitNumber, $this->helper->getLimitNumberPasswordResetRequests($scope));
    }

    /**
     * @param int $limitTime
     * @param int $scope
     * @dataProvider dataProviderNumberValueWithScope
     */
    public function testGetLimitTimeBetweenPasswordResetRequests($limitTime, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathByScope($scope)
                . \Magento\Security\Helper\SecurityConfig::XML_PATH_LIMIT_TIME_BETWEEN_REQUESTS
            )
            ->will(
                $this->returnValue($limitTime)
            );
        $this->assertEquals($limitTime * 60, $this->helper->getLimitTimeBetweenPasswordResetRequests($scope));
    }

    /**
     * @return array
     */
    public function dataProviderNumberValueWithScope()
    {
        return [
            [5, \Magento\Security\Helper\SecurityConfig::FRONTED_AREA_SCOPE],
            [5, \Magento\Security\Helper\SecurityConfig::ADMIN_AREA_SCOPE]
        ];
    }

    /**
     * @return void
     */
    public function testGetCurrentTimestamp()
    {
        $this->assertEquals(true, is_int($this->helper->getCurrentTimestamp()));
    }
}
