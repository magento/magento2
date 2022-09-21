<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\UserProfile;
use Magento\AdobeImsApi\Api\Data\UserProfileExtensionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * User profile test.
 *
 * Tests all setters and getters of data transport class
 */
class UserProfileTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserProfile $model
     */
    private $model;

    /**
     * Prepare test object.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(UserProfile::class);
    }

    /**
     * Test setAccessToken
     */
    public function testAccessToken(): void
    {
        $value = 'value1';
        $this->model->setAccessToken($value);
        $this->assertSame($value, $this->model->getAccessToken());
    }

    /**
     * Test setRefreshToken
     */
    public function testRefreshToken(): void
    {
        $value = 'value1';
        $this->model->setRefreshToken($value);
        $this->assertSame($value, $this->model->getRefreshToken());
    }

    /**
     * Test setAccessTokenExpiresAt
     */
    public function testAccessTokenExpiresAt(): void
    {
        $value = 'value1';
        $this->model->setAccessTokenExpiresAt($value);
        $this->assertSame($value, $this->model->getAccessTokenExpiresAt());
    }

    /**
     * Test setCreatedAt
     */
    public function testCreatedAt(): void
    {
        $value = 'value1';
        $this->model->setCreatedAt($value);
        $this->assertSame($value, $this->model->getCreatedAt());
    }

    /**
     * Test setUpdatedAt
     */
    public function testUpdatedAt(): void
    {
        $value = 'value1';
        $this->model->setUpdatedAt($value);
        $this->assertSame($value, $this->model->getUpdatedAt());
    }

    /**
     * Test setAccountType
     */
    public function testAccountType(): void
    {
        $value = 'value1';
        $this->model->setAccountType($value);
        $this->assertSame($value, $this->model->getAccountType());
    }

    /**
     * Test setEmail
     */
    public function testEmail(): void
    {
        $value = 'value1';
        $this->model->setEmail($value);
        $this->assertSame($value, $this->model->getEmail());
    }

    /**
     * Test setImage
     */
    public function testImage(): void
    {
        $value = 'value1';
        $this->model->setImage($value);
        $this->assertSame($value, $this->model->getImage());
    }

    /**
     * Test setName
     */
    public function testName(): void
    {
        $value = 'value1';
        $this->model->setName($value);
        $this->assertSame($value, $this->model->getName());
    }

    /**
     * Test setUserId
     */
    public function testUserId(): void
    {
        $value = 42;
        $this->model->setUserId($value);
        $this->assertSame($value, $this->model->getUserId());
    }

    /**
     * Test setExtensionAttributes
     */
    public function testExtensionAttributes(): void
    {
        $value = $this->createMock(UserProfileExtensionInterface::class);
        $this->model->setExtensionAttributes($value);
        $this->assertSame($value, $this->model->getExtensionAttributes());
    }
}
