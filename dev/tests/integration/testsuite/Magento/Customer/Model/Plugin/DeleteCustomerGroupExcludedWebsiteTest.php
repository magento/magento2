<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsite;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;

/**
 * Checks that removal of website also deletes it from the customer group excluded website table.
 * @magentoAppArea adminhtml
 */
class DeleteCustomerGroupExcludedWebsiteTest extends \PHPUnit\Framework\TestCase
{
    private const GROUP_CODE = 'Humans';
    private const STORE_WEBSITE_CODE = 'custom_website';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var GroupInterfaceFactory */
    private $groupFactory;

    /** @var WebsiteResourceModel */
    private $websiteResourceModel;

    /** @var ResourceConnection */
    private $resourceConnection;

    /** @var \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory */
    private $groupExtensionInterfaceFactory;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->groupRepository = $this->objectManager->create(GroupRepositoryInterface::class);
        $this->groupFactory = $this->objectManager->create(GroupInterfaceFactory::class);
        $this->websiteResourceModel = $this->objectManager->get(WebsiteResourceModel::class);
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->groupExtensionInterfaceFactory = $this->objectManager
            ->get(\Magento\Customer\Api\Data\GroupExtensionInterfaceFactory::class);
        $this->websiteRepository = $this->objectManager->create(WebsiteRepositoryInterface::class);
        $this->registry = $this->objectManager->create(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** Marks area as secure so Product repository would allow group removal */
        $isSecuredAreaSystemState = $this->registry->registry('isSecuredArea');
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        /** Remove customer group */
        $groupId = $this->findGroupIdWithCode(self::GROUP_CODE);
        $group = $this->groupRepository->getById($groupId);
        $this->groupRepository->delete($group);

        /** Revert mark area secured */
        $this->registry->unregister('isSecuredArea');
        $this->registry->register('isSecuredArea', $isSecuredAreaSystemState);
    }

    /**
     * Test that deletion of website also deletes this website from customer group excluded websites.
     * @magentoDbIsolation disabled
     */
    public function testDeleteExcludedWebsiteAfterWebsiteDelete(): void
    {
        /** Create website */
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->setName('custom website for delete excluded website test')
            ->setCode(self::STORE_WEBSITE_CODE);
        $website->isObjectNew(true);
        $this->websiteResourceModel->save($website);
        $websiteId = $this->websiteRepository->get(self::STORE_WEBSITE_CODE)->getId();

        /** Create a new customer group */
        $group = $this->groupFactory->create()
            ->setId(null)
            ->setCode(self::GROUP_CODE)
            ->setTaxClassId(3);
        $groupId = $this->groupRepository->save($group)->getId();
        self::assertNotNull($groupId);

        /** Exclude website from customer group */
        $group = $this->groupRepository->getById($groupId);
        $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
        $customerGroupExtensionAttributes->setExcludeWebsiteIds([$websiteId]);
        $group->setExtensionAttributes($customerGroupExtensionAttributes);
        $this->groupRepository->save($group);

        /** Check that excluded website is in customer group excluded website table */
        $connection = $this->resourceConnection->getConnection();
        $selectExcludedWebsite = $connection->select();
        /** @var GroupExcludedWebsite $groupExcludedWebsiteResource */
        $groupExcludedWebsiteResource = $this->objectManager->create(GroupExcludedWebsite::class);
        $selectExcludedWebsite->from($groupExcludedWebsiteResource->getMainTable())
            ->where('website_id = ?', $websiteId);
        $excludedWebsites = $connection->fetchAll($selectExcludedWebsite);
        self::assertCount(1, $excludedWebsites);

        /** Marks area as secure so Product repository would allow website removal */
        $registry = $this->objectManager->get(Registry::class);
        $isSecuredAreaSystemState = $registry->registry('isSecuredArea');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** Remove website by id */
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->objectManager->create(\Magento\Store\Model\Website::class);
        $website->load((int)$websiteId);
        $website->delete();

        /** Revert mark area secured */
        $registry->unregister('isSecuredArea');
        $registry->register('isSecuredArea', $isSecuredAreaSystemState);

        /** Check that excluded website is no longer in customer group excluded website table */
        $selectExcludedWebsite = $connection->select();
        /** @var GroupExcludedWebsite $groupExcludedWebsiteResource */
        $groupExcludedWebsiteResource = $this->objectManager->create(GroupExcludedWebsite::class);
        $selectExcludedWebsite->from($groupExcludedWebsiteResource->getMainTable())
            ->where('website_id = ?', $websiteId);
        $excludedWebsites = $connection->fetchAll($selectExcludedWebsite);
        self::assertCount(0, $excludedWebsites);
    }

    /**
     * Find the customer group with a given code.
     *
     * @param string $code
     * @return int
     * @throws LocalizedException
     */
    private function findGroupIdWithCode(string $code): int
    {
        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->objectManager->create(GroupRepositoryInterface::class);
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);

        foreach ($groupRepository->getList($searchBuilder->create())->getItems() as $group) {
            if ($group->getCode() === $code) {
                return (int)$group->getId();
            }
        }

        return -1;
    }
}
