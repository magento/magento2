<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Exception\State\SubscriptionUpdateException;
use Magento\Framework\FlagManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ReportUrlProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReportUrlProvider
     */
    private $reportUrlProvider;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->reportUrlProvider = $objectManager->get(ReportUrlProvider::class);
        $this->flagManager = $objectManager->get(FlagManager::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetUrlWhenSubscriptionUpdateIsRunning()
    {
        $this->flagManager
            ->saveFlag(
                SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE,
                'https://previous.example.com/'
            );
        $this->expectException(SubscriptionUpdateException::class);
        $this->reportUrlProvider->getUrl();
    }
}
