<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as AttributeStatus;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;

/**
 * Checks excluding websites from customer group functionality that affects price and catalog rule indexes.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupExcludedWebsiteTest extends \PHPUnit\Framework\TestCase
{
    private const GROUP_CODE = 'Aliens';
    private const STORE_WEBSITE_CODE = 'customwebsite1';
    private const STORE_GROUP_CODE = 'customstoregroup1';
    private const STORE_CODE = 'customstoreview1';
    private const PRODUCT_ID = 333;
    private const CATEGORY_ID = 444;
    private const CUSTOMER_EMAIL = 'first_last@example.com';
    private const SKU = 'simplecustomproduct';

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var GroupInterfaceFactory */
    private $groupFactory;

    /** @var WebsiteResourceModel */
    private $websiteResourceModel;

    /** @var StoreGroupResourceModel */
    private $storeGroupResourceModel;

    /** @var StoreResourceModel */
    private $storeResourceModel;

    /** @var ProductRepositoryInterface  */
    private $productRepository;

    /** @var ResourceConnection */
    private $resourceConnection;

    /** @var \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory */
    private $groupExtensionInterfaceFactory;

    /** @var IndexerRegistry */
    private $indexRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customerFactory = $this->objectManager->create(CustomerInterfaceFactory::class);
        $this->groupRepository = $this->objectManager->create(GroupRepositoryInterface::class);
        $this->groupFactory = $this->objectManager->create(GroupInterfaceFactory::class);
        $this->websiteResourceModel = $this->objectManager->get(WebsiteResourceModel::class);
        $this->storeGroupResourceModel = $this->objectManager->get(StoreGroupResourceModel::class);
        $this->storeResourceModel = $this->objectManager->get(StoreResourceModel::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->groupExtensionInterfaceFactory = $this->objectManager
            ->get(\Magento\Customer\Api\Data\GroupExtensionInterfaceFactory::class);
        $this->indexRegistry = $this->objectManager->create(IndexerRegistry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $registry = $this->objectManager->get(Registry::class);
        /** Marks area as secure so Product repository would allow product removal */
        $isSecuredAreaSystemState = $registry->registry('isSecuredArea');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        /** @var AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        /** @var \Magento\Store\Model\Store $store */
        $store = $storeRepository->get(self::STORE_CODE);
        $storeGroupId = $store->getStoreGroupId();
        $websiteId = $store->getWebsiteId();

        /** Remove product */
        $product = $productRepository->getById(self::PRODUCT_ID);
        if ($product->getId()) {
            $productRepository->delete($product);
        }

        /** Remove customer */
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->get(self::CUSTOMER_EMAIL, $websiteId);
        $this->customerRepository->delete($customer);

        /** Remove customer group */
        $groupId = $this->findGroupIdWithCode(self::GROUP_CODE);
        $group = $this->groupRepository->getById($groupId);
        $this->groupRepository->delete($group);

        /** Remove category */
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->load(self::CATEGORY_ID);
        if ($category->getId()) {
            $category->delete();
        }

        /** Remove store by code */
        $storeCodes = [self::STORE_CODE];
        $connection->delete(
            $this->resourceConnection->getTableName('store'),
            ['code IN (?)' => $storeCodes]
        );

        /** Remove store group by id*/
        $connection->delete(
            $this->resourceConnection->getTableName('store_group'),
            ['group_id = ?' => $storeGroupId]
        );

        /** Remove website by id */
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->objectManager->create(\Magento\Store\Model\Website::class);
        $website->load((int)$websiteId);
        $website->delete();

        /** Remove catalog rule */
        /** @var RuleResourceModel $catalogRuleResource */
        $catalogRuleResource = $this->objectManager->create(RuleResourceModel::class);
        $select = $connection->select();
        $select->from($catalogRuleResource->getMainTable(), 'rule_id');
        $select->where('name = ?', 'Test Catalog Rule With 50 Percent Off');
        $ruleId = $connection->fetchOne($select);
        /** @var CatalogRuleRepositoryInterface $ruleRepository */
        $ruleRepository = $this->objectManager->create(CatalogRuleRepositoryInterface::class);
        $ruleRepository->deleteById($ruleId);

        /** @var IndexerCollection $indexerCollection */
        $indexerCollection = $this->objectManager->get(IndexerCollection::class);
        $indexerCollection->load();
        foreach ($indexerCollection->getItems() as $indexer) {
            /** @var Indexer $indexer */
            $indexer->reindexAll();
        }

        /** Revert mark area secured */
        $registry->unregister('isSecuredArea');
        $registry->register('isSecuredArea', $isSecuredAreaSystemState);
    }

    /**
     * Test excluding website from customer group
     *
     * @magentoDbIsolation disabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCustomerGroupExcludeWebsite(): void
    {
        /** Create website */
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->setName('custom website for customer group limitations test')
            ->setCode(self::STORE_WEBSITE_CODE);
        $website->isObjectNew(true);
        $this->websiteResourceModel->save($website);

        /** Create store group */
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->setCode(self::STORE_GROUP_CODE)
            ->setName('custom store group for customer group limitations test')
            ->setWebsite($website);
        $this->storeGroupResourceModel->save($storeGroup);

        $website->setDefaultGroupId($storeGroup->getId());
        $this->websiteResourceModel->save($website);

        /** Create store */
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->setName('custom store for customer group limitations test')
            ->setCode(self::STORE_CODE)
            ->setGroup($storeGroup);
        $store->setWebsite($website);
        $this->storeResourceModel->save($store);

        $storeId = $store->getId();
        $storeGroup->setDefaultStoreId($storeId);
        $websiteId = $store->getWebsiteId();
        $this->storeGroupResourceModel->save($storeGroup);

        /** Create a new customer group */
        $group = $this->groupFactory->create()
            ->setId(null)
            ->setCode(self::GROUP_CODE)
            ->setTaxClassId(3);
        $groupId = $this->groupRepository->save($group)->getId();
        self::assertNotNull($groupId);

        /** Create a new customer */
        $firstname = 'First';
        $lastname = 'Last';
        $newCustomerEntity = $this->customerFactory->create()
            ->setGroupId($groupId)
            ->setStoreId($storeId)
            ->setEmail(self::CUSTOMER_EMAIL)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setWebsiteId($websiteId);
        $this->customerRepository->save($newCustomerEntity);

        /** Create new category */
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->isObjectNew(true);
        $category->setId(self::CATEGORY_ID)
            ->setCreatedAt('2020-06-23 09:50:07')
            ->setName('Misc')
            ->setParentId(2)
            ->setPath('1/2/444')
            ->setLevel(2)
            ->setAvailableSortBy(['position', 'name'])
            ->setIsActive(true)
            ->setPosition(1)
            ->setStoreId($storeId)
            ->save();

        /** Create product */
        /** @var $product Product */
        $product = $this->objectManager->create(Product::class);
        $product->isObjectNew(true);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setId(self::PRODUCT_ID)
            ->setAttributeSetId(4)
            ->setName('Simple Product custom')
            ->setSku(self::SKU)
            ->setTaxClassId('none')
            ->setDescription('description')
            ->setShortDescription('short description')
            ->setOptionsContainer('container1')
            ->setMsrpDisplayActualPriceType(\Magento\Msrp\Model\Product\Attribute\Source\Type::TYPE_IN_CART)
            ->setPrice(10)
            ->setWeight(1)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(AttributeStatus::STATUS_ENABLED)
            ->setWebsiteIds([$websiteId])
            ->setCategoryIds([self::CATEGORY_ID])
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

        $this->productRepository->save($product);
        $product = $this->productRepository->get(self::SKU);
        $productId = $product->getId();

        /** Create catalog rule */
        $catalogRule = $this->objectManager->create(Rule::class);
        $catalogRule
            ->setIsActive(1)
            ->setName('Test Catalog Rule With 50 Percent Off')
            ->setCustomerGroupIds($groupId)
            ->setDiscountAmount(50)
            ->setWebsiteIds([$websiteId])
            ->setSimpleAction('by_percent')
            ->setStopRulesProcessing(false)
            ->setSortOrder(0)
            ->setSubIsEnable(0)
            ->setSubDiscountAmount(0)
            ->save();
        $this->reindexPriceAndCatalogRule();

        /** Check that there is no customer group excluded website in price or catalog rule indexes */
        $this->checkNoExcludedWebsite((int)$websiteId, (int)$groupId, (int)$productId);

        /** Exclude website from customer group */
        $group = $this->groupRepository->getById($groupId);
        $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
        $customerGroupExtensionAttributes->setExcludeWebsiteIds([$websiteId]);
        $group->setExtensionAttributes($customerGroupExtensionAttributes);
        $this->groupRepository->save($group);

        $this->reindexPriceAndCatalogRule();

        /** Check that excluding website from customer group affects catalog rule */
        $resource = $resource = $this->objectManager->get(RuleResourceModel::class);
        $date = $this->objectManager->get(DateTime::class);
        $rules = $resource->getRulesFromProduct($date->gmtDate(), $websiteId, $groupId, $productId);
        self::assertCount(0, $rules);
        // check that excluded website is eliminated from catalogrule_group_website table
        $connection = $this->resourceConnection->getConnection();
        $selectCatalogRuleGroupWebsite = $connection->select();
        $selectCatalogRuleGroupWebsite->from('catalogrule_group_website')
            ->where('customer_group_id = ?', $groupId);
        $catalogRuleGroupWebsites = $connection->fetchAll($selectCatalogRuleGroupWebsite);
        self::assertCount(0, $catalogRuleGroupWebsites);

        /** Check that excluding website from customer group affects price index */
        /** @var Price $catalogProductIndexPriceResource */
        $catalogProductIndexPriceResource = $this->objectManager->create(Price::class);
        $select = $connection->select();
        $select->from($catalogProductIndexPriceResource->getMainTable());
        $select->where('customer_group_id = ?', $groupId);
        $prices = $connection->fetchAll($select);
        self::assertCount(0, $prices);

        /** Delete excluded website from customer group */
        $group = $this->groupRepository->getById($groupId);
        $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
        $customerGroupExtensionAttributes->setExcludeWebsiteIds([]);
        $group->setExtensionAttributes($customerGroupExtensionAttributes);
        $this->groupRepository->save($group);

        $this->reindexPriceAndCatalogRule();

        /** Check that there is no excluded website from customer group in price or catalog rule indexes */
        $this->checkNoExcludedWebsite((int)$websiteId, (int)$groupId, (int)$productId);
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

    /**
     * Reindex product price and catalog rule indexes.
     *
     * @throws \Exception
     */
    private function reindexPriceAndCatalogRule(): void
    {
        $priceIndexer = $this->indexRegistry->get(Processor::INDEXER_ID);
        $priceIndexer->reindexAll();
        $catalogRuleIndexer = $this->indexRegistry->get(ProductRuleProcessor::INDEXER_ID);
        $catalogRuleIndexer->reindexAll();
    }

    /**
     * Check that there is no customer group excluded website in price or catalog rule indexes.
     *
     * @param int $websiteId
     * @param int $groupId
     * @param int $productId
     * @return void
     * @throws LocalizedException
     */
    private function checkNoExcludedWebsite(int $websiteId, int $groupId, int $productId): void
    {
        /** Check catalog rule */
        $date = $this->objectManager->create(DateTime::class);
        $dateTs = $date->gmtDate();
        /** @var RuleResourceModel $resource */
        $resource = $this->objectManager->create(RuleResourceModel::class);
        $rules = $resource->getRulesFromProduct($dateTs, $websiteId, $groupId, $productId);
        self::assertCount(1, $rules);
        foreach ($rules as $rule) {
            self::assertEquals($groupId, $rule['customer_group_id']);
            self::assertEquals($websiteId, $rule['website_id']);
        }

        $connection = $this->resourceConnection->getConnection();
        $selectCatalogRuleGroupWebsite = $connection->select();
        $selectCatalogRuleGroupWebsite->from('catalogrule_group_website')
            ->where('customer_group_id = ?', $groupId);
        $catalogRuleGroupWebsites = $connection->fetchAll($selectCatalogRuleGroupWebsite);
        self::assertCount(1, $catalogRuleGroupWebsites);
        foreach ($catalogRuleGroupWebsites as $catalogRuleGroupWebsite) {
            self::assertEquals($groupId, $catalogRuleGroupWebsite['customer_group_id']);
            self::assertEquals($websiteId, $catalogRuleGroupWebsite['website_id']);
        }

        /** Check price index */
        /** @var Price $catalogProductIndexPriceResource */
        $catalogProductIndexPriceResource = $this->objectManager->create(Price::class);
        $select = $connection->select();
        $select->from($catalogProductIndexPriceResource->getMainTable());
        $select->where('customer_group_id = ?', $groupId);
        $prices = $connection->fetchAll($select);
        self::assertCount(1, $prices);
        foreach ($prices as $price) {
            self::assertEquals($groupId, $price['customer_group_id']);
            self::assertEquals($websiteId, $price['website_id']);
        }
    }
}
