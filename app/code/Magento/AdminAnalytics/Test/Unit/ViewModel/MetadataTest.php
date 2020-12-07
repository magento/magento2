<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\ViewModel;

use Magento\AdminAnalytics\ViewModel\Metadata;
use Magento\Authorization\Model\Role;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Information;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var ProductMetadataInterface|MockObject
     */
    private $productMetadata;

    /**
     * @var Session|MockObject
     */
    private $authSession;

    /**
     * @var State|MockObject
     */
    private $appState;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $config;

    protected function setUp(): void
    {
        $this->productMetadata = $this->createMock(ProductMetadataInterface::class);
        $this->authSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $this->appState = $this->createMock(State::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->metadata = $objectManager->getObject(
            Metadata::class,
            [
                'productMetadata' => $this->productMetadata,
                'authSession' => $this->authSession,
                'appState' => $this->appState,
                'config' => $this->config,
            ]
        );
    }

    public function testGetMagentoVersion()
    {
        $version = '1.1.1';
        $this->productMetadata
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);

        $this->assertSame($version, $this->metadata->getMagentoVersion());
    }

    public function testGetProductEdition()
    {
        $productEdition = 'edition_fake';

        $this->productMetadata
            ->expects($this->once())
            ->method('getEdition')
            ->willReturn($productEdition);

        $this->assertSame(
            $productEdition,
            $this->metadata->getProductEdition()
        );
    }

    public function testGetCurrentUser()
    {
        $user = $this->createMock(User::class);
        $email = 'phamkhien@hotmail.com';

        $this->authSession
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $user->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->assertSame(
            hash('sha256', 'ADMIN_USER' . $email),
            $this->metadata->getCurrentUser()
        );
    }

    public function testGetCurrentUserCreatedDate()
    {
        $user = $this->createMock(User::class);

        $createdDate = '2020-12-19 13-03-45';
        $this->authSession
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $user->expects($this->once())
            ->method('getCreated')
            ->willReturn($createdDate);

        $this->assertSame(
            $createdDate,
            $this->metadata->getCurrentUserCreatedDate()
        );
    }

    public function testGetCurrentUserLogDate()
    {
        $user = $this->getMockBuilder(User::class)
            ->setMethods(['getLogdate'])
            ->disableOriginalConstructor()
            ->getMock();

        $createdDate = '2020-12-01 13-01-01';
        $this->authSession
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $user->expects($this->once())
            ->method('getLogdate')
            ->willReturn($createdDate);

        $this->assertSame(
            $createdDate,
            $this->metadata->getCurrentUserLogDate()
        );
    }

    public function testGetSecureBaseUrlForScope()
    {
        $scope = 'default';
        $scopeCode = 'th';
        $result = 'test';

        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->with(Custom::XML_PATH_SECURE_BASE_URL, $scope, $scopeCode)
            ->willReturn($result);

        $this->assertSame(
            $result,
            $this->metadata->getSecureBaseUrlForScope($scope, $scopeCode)
        );
    }

    public function testGetStoreNameForScope()
    {
        $scope = 'default';
        $scopeCode = 'th';
        $result = 'test_fake_GetStoreNameForScope';

        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->with(Information::XML_PATH_STORE_INFO_NAME, $scope, $scopeCode)
            ->willReturn($result);

        $this->assertSame(
            $result,
            $this->metadata->getStoreNameForScope($scope, $scopeCode)
        );
    }

    public function testGetCurrentUserRoleName()
    {
        $user = $this->createMock(User::class);
        $role = $this->getMockBuilder(Role::class)
            ->setMethods(['getRoleName'])
            ->disableOriginalConstructor()
            ->getMock();
        $roleName = 'khien';

        $this->authSession
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $user->expects($this->once())
            ->method('getRole')
            ->willReturn($role);
        $role->expects($this->once())
            ->method('getRoleName')
            ->willReturn($roleName);

        $this->assertSame(
            $roleName,
            $this->metadata->getCurrentUserRoleName()
        );
    }

    public function testGetMode()
    {
        $mode = 'developer_test';

        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);

        $this->assertSame($mode, $this->metadata->getMode());
    }
}
