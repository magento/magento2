<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\Unit\Model;

use Magento\Security\Model\ConfigInterface;

/**
 * Test class for \Magento\Security\Model\Config testing
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $scopeMock;

    /**
     * @var ConfigInterface
     */
    protected $model;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock =  $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );

        $this->scopeMock =  $this->getMock(
            \Magento\Framework\Config\ScopeInterface::class,
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Security\Model\Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'scope' => $this->scopeMock
            ]
        );
    }

    public function testGetLimitationTimePeriod()
    {
        $this->assertEquals(
            \Magento\Security\Model\Config::LIMITATION_TIME_PERIOD,
            $this->model->getLimitationTimePeriod()
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
                \Magento\Security\Model\Config::XML_PATH_EMAIL_RECIPIENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will(
                $this->returnValue($email)
            );
        $this->assertEquals($email, $this->model->getCustomerServiceEmail());
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
        $this->assertEquals($lifetime, $this->model->getAdminSessionLifetime());
    }

    /**
     * @param bool $isShared
     * @dataProvider dataProviderBoolValues
     */
    public function testIsAdminAccountSharingIsEnabled($isShared)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(\Magento\Security\Model\Config::XML_PATH_ADMIN_ACCOUNT_SHARING)
            ->will(
                $this->returnValue($isShared)
            );
        $this->assertEquals($isShared, $this->model->isAdminAccountSharingEnabled());
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
    public function testGetPasswordResetProtectionType($resetMethod, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathPrefix($scope)
                . \Magento\Security\Model\Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE
            )
            ->willReturn($resetMethod);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($scope);
        $this->assertEquals($resetMethod, $this->model->getPasswordResetProtectionType($scope));
    }

    /**
     * @return array
     */
    public function dataProviderResetMethodValues()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $resetMethodSource = $objectManager->getObject(
            \Magento\Security\Model\Config\Source\ResetMethod::class
        );

        $optionKeys = array_keys($resetMethodSource->toArray());
        $data = [];
        foreach ($optionKeys as $key) {
            $data[] = [$key, \Magento\Framework\App\Area::AREA_ADMINHTML];
            $data[] = [$key, \Magento\Framework\App\Area::AREA_FRONTEND];
        }

        return $data;
    }

    /**
     * Get xml path by scope
     *
     * @param int $scope
     * @return string
     */
    protected function getXmlPathPrefix($scope)
    {
        if ($scope == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return \Magento\Security\Model\Config::XML_PATH_ADMIN_AREA;
        }
        return \Magento\Security\Model\Config::XML_PATH_FRONTED_AREA;
    }

    /**
     * @param int $limitNumber
     * @param int $scope
     * @dataProvider dataProviderNumberValueWithScope
     */
    public function testGetMaxNumberPasswordResetRequests($limitNumber, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathPrefix($scope)
                . \Magento\Security\Model\Config::XML_PATH_MAX_NUMBER_PASSWORD_RESET_REQUESTS
            )
            ->willReturn($limitNumber);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($scope);
        $this->assertEquals($limitNumber, $this->model->getMaxNumberPasswordResetRequests());
    }

    /**
     * @param int $limitTime
     * @param int $scope
     * @dataProvider dataProviderNumberValueWithScope
     */
    public function testGetMinTimeBetweenPasswordResetRequests($limitTime, $scope)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->getXmlPathPrefix($scope)
                . \Magento\Security\Model\Config::XML_PATH_MIN_TIME_BETWEEN_PASSWORD_RESET_REQUESTS
            )
            ->willReturn($limitTime);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($scope);
        $this->assertEquals($limitTime * 60, $this->model->getMinTimeBetweenPasswordResetRequests());
    }

    /**
     * @return array
     */
    public function dataProviderNumberValueWithScope()
    {
        return [
            [5, \Magento\Framework\App\Area::AREA_ADMINHTML],
            [5, \Magento\Framework\App\Area::AREA_FRONTEND]
        ];
    }
}
