<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\ViewModel;

use Magento\AdminAnalytics\ViewModel\Metadata;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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

    protected function setUp(): void
    {
        $this->productMetadata = $this->createMock(ProductMetadataInterface::class);
        $this->authSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $this->appState = $this->createMock(State::class);

        $objectManager = new ObjectManager($this);
        $this->metadata = $objectManager->getObject(
            Metadata::class,
            [
                'productMetadata' => $this->productMetadata,
                'authSession' => $this->authSession,
                'appState' => $this->appState,
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
            hash('sha512', 'ADMIN_USER' . $email),
            $this->metadata->getCurrentUser()
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
