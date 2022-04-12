<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model;

use Magento\AdminAdobeIms\Model\ImsWebapi;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiExtensionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * User profile test.
 *
 * Tests all setters and getters of data transport class
 */
class ImsWebapiTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ImsWebapi $model
     */
    private $model;

    /**
     * Prepare test object.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(ImsWebapi::class);
    }

    /**
     * Test setAccessToken
     */
    public function testAccessTokenHash(): void
    {
        $value = 'value1';
        $this->model->setAccessTokenHash($value);
        $this->assertSame($value, $this->model->getAccessTokenHash());
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
     * Test setAdminUserId
     */
    public function testAdminUserId(): void
    {
        $value = 42;
        $this->model->setAdminUserId($value);
        $this->assertSame($value, $this->model->getAdminUserId());
    }

    /**
     * Test setExtensionAttributes
     */
    public function testExtensionAttributes(): void
    {
        $value = $this->createMock(ImsWebapiExtensionInterface::class);
        $this->model->setExtensionAttributes($value);
        $this->assertSame($value, $this->model->getExtensionAttributes());
    }
}
