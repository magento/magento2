<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test \Magento\Customer\Model\Config\Share
 */
class ShareTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Share */
    private $share;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->share = $this->objectManager->get(Share::class);
        $this->websiteRepository = $this->objectManager->create(WebsiteRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testGetSharedWebsiteIds(): void
    {
        $websiteIds = $this->share->getSharedWebsiteIds(42);
        $this->assertEquals([42], $websiteIds);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoConfigFixture current_store customer/account_share/scope 0
     *
     * @return void
     */
    public function testGetSharedWebsiteIdsMultipleSites(): void
    {
        $expectedIds[] = $this->websiteRepository->get('base')->getId();
        $expectedIds[] = $this->websiteRepository->get('secondwebsite')->getId();
        $expectedIds[] = $this->websiteRepository->get('thirdwebsite')->getId();
        $websiteIds = $this->share->getSharedWebsiteIds(42);
        $this->assertEquals($expectedIds, $websiteIds);
    }

    /**
     * @magentoConfigFixture current_store customer/account_share/scope 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_for_second_website.php
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testEnableGlobalAccountShareScope(): void
    {
        $message = 'We can\'t share customer accounts globally when the accounts share'
            . ' identical email addresses on more than one website.';
        $this->expectExceptionObject(new LocalizedException(__($message)));
        $this->share->setPath(Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE)->setValue((string)Share::SHARE_GLOBAL)
            ->beforeSave();
    }
}
