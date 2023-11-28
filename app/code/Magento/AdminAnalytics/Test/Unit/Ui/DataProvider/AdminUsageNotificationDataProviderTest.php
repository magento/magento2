<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\Ui\DataProvider;

use Magento\AdminAnalytics\Ui\DataProvider\AdminUsageNotificationDataProvider;
use Magento\Framework\Api\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AdminUsageNotificationDataProviderTest extends TestCase
{
    /**
     * @var AdminUsageNotificationDataProvider
     */
    private $adminUsageNotificationDataProvider;

    /**
     * @var array
     */
    private $data;

    protected function setUp(): void
    {
        $this->data = ['test'];

        $objectManager = new ObjectManager($this);
        $this->adminUsageNotificationDataProvider = $objectManager->getObject(
            AdminUsageNotificationDataProvider::class,
            ['data' => $this->data]
        );
    }

    public function testGetData()
    {
        $this->assertSame($this->data, $this->adminUsageNotificationDataProvider->getData());
    }

    public function testAddFilter()
    {
        $this->assertNull($this->adminUsageNotificationDataProvider->addFilter($this->createMock(Filter::class)));
    }
}
