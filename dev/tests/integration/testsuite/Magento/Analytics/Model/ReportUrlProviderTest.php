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
class ReportUrlProviderTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
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
        $this->setExpectedException(
            SubscriptionUpdateException::class,
            'Your Base URL has been changed and your reports are being updated. '
            . 'Advanced Reporting will be available once this change has been processed. Please try again later.'
        );
        $this->reportUrlProvider->getUrl();
    }
}
