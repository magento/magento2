<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Integration\Model\ResourceModel\Inbox\Collection;

use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CriticalTest extends TestCase
{
    /**
     * @var Critical
     */
    private $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(
            Critical::class
        );
    }

    /**
     * @magentoDataFixture Magento_AdminNotification::Test/Integration/_files/notifications.php
     *
     * @return void
     */
    public function testCollectionContainsLastUnreadCriticalItem(): void
    {
        $items = array_values($this->model->getItems());
        $this->assertEquals('Unread Critical 3', $items[0]->getTitle());
    }
}
