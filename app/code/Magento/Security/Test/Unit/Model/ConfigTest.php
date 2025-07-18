<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\Config;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Config testing
 */
class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var ScopeInterface
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
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createPartialMock(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );

        $this->scopeMock =  $this->getMockForAbstractClass(ScopeInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'scope' => $this->scopeMock
            ]
        );
    }

    public function testGetLimitationTimePeriod()
    {
        $this->assertEquals(
            Config::LIMITATION_TIME_PERIOD,
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
                Config::XML_PATH_EMAIL_RECIPIENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(
                $email
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
            ->with(Session::XML_PATH_SESSION_LIFETIME)
            ->willReturn(
                $lifetime
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
            ->with(Config::XML_PATH_ADMIN_ACCOUNT_SHARING)
            ->willReturn(
                $isShared
            );
        $this->assertEquals($isShared, $this->model->isAdminAccountSharingEnabled());
    }

    /**
     * @return array
     */
    public static function dataProviderBoolValues()
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
                . Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE
            )
            ->willReturn($resetMethod);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($scope);
        $this->assertEquals($resetMethod, $this->model->getPasswordResetProtectionType());
    }

    /**
     * @return array
     */
    public static function dataProviderResetMethodValues()
    {
        $resetMethodSource = new ResetMethod();

        $optionKeys = array_keys($resetMethodSource->toArray());
        $data = [];
        foreach ($optionKeys as $key) {
            $data[] = [$key, Area::AREA_ADMINHTML];
            $data[] = [$key, Area::AREA_FRONTEND];
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
        if ($scope == Area::AREA_ADMINHTML) {
            return Config::XML_PATH_ADMIN_AREA;
        }
        return Config::XML_PATH_FRONTEND_AREA;
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
                . Config::XML_PATH_MAX_NUMBER_PASSWORD_RESET_REQUESTS
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
                . Config::XML_PATH_MIN_TIME_BETWEEN_PASSWORD_RESET_REQUESTS
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
    public static function dataProviderNumberValueWithScope()
    {
        return [
            [5, Area::AREA_ADMINHTML],
            [5, Area::AREA_FRONTEND]
        ];
    }
}
