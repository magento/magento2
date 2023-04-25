<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;

/**
 * Generate websites, store groups and store views based on profile configuration
 * Supports next format:
 * <websites>{amount of websites}</websites>
 * <store_groups>{amount of store groups}</store_groups>
 * <store_views>{amount of store views}</store_views>
 * <assign_entities_to_all_websites>{1|0}</assign_entities_to_all_websites>
 *
 * Each node of configuration except <assign_entities_to_all_websites/>
 * means how many entities need to be generated
 *
 * Store groups will have normal distribution among websites
 * Store views will have normal distribution among store groups
 *
 * <assign_entities_to_all_websites>1<assign_entities_to_all_websites/>
 * means that all stores will have the same root category
 *
 * <assign_entities_to_all_websites>0<assign_entities_to_all_websites/>
 * means that all stores will have unique root category
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @SuppressWarnings(PHPMD)
 */
class StoresFixture extends Fixture
{
    private const DEFAULT_WEBSITE_COUNT = 1;

    private const DEFAULT_STORE_COUNT = 1;

    private const DEFAULT_STORE_VIEW_COUNT = 1;

    /**
     * @var int
     */
    protected $priority = 10;

    /**
     * @var array
     */
    private $websiteIds = [];

    /**
     * @var array
     */
    private $storeGroupsIds = [];

    /**
     * @var array
     */
    private $storeGroupsToWebsites = [];

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Writer
     */
    private $scopeConfig;

    /**
     * @var Group
     */
    private $defaultStoreGroup;

    /**
     * @var Website
     */
    private $defaultWebsite;

    /**
     * @var int
     */
    private $defaultParentCategoryId;

    /**
     * @var int
     */
    private $defaultStoreGroupId;

    /**
     * @var int
     */
    private $defaultWebsiteId;

    /**
     * @var int
     */
    private $storeGroupsCount;

    /**
     * @var int
     */
    private $storesCount;

    /**
     * @var int
     */
    private $websitesCount;

    /**
     * @var bool
     */
    private $singleRootCategory;

    /**
     * @var StoreInterface
     */
    private $defaultStoreView;

    /**
     * @var int
     */
    private $storeViewIds;

    /**
     * @var string[]
     */
    private $websiteCodes = [];

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Config
     */
    private $localeConfig;

    /**
     * StoresFixture constructor
     * @param FixtureModel $fixtureModel
     * @param StoreManager $storeManager
     * @param ManagerInterface $eventManager
     * @param CategoryFactory $categoryFactory
     * @param Config $localeConfig
     * @param Writer $scopeConfig
     */
    public function __construct(
        FixtureModel $fixtureModel,
        StoreManager $storeManager,
        ManagerInterface $eventManager,
        CategoryFactory $categoryFactory,
        Config $localeConfig,
        Writer $scopeConfig
    ) {
        parent::__construct($fixtureModel);
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->categoryFactory = $categoryFactory;
        $this->localeConfig = $localeConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        //get settings counts
        $this->websitesCount = $this->fixtureModel->getValue('websites', self::DEFAULT_WEBSITE_COUNT);
        $this->storeGroupsCount = $this->fixtureModel->getValue('store_groups', self::DEFAULT_STORE_COUNT);
        $this->storesCount = $this->fixtureModel->getValue('store_views', self::DEFAULT_STORE_VIEW_COUNT);
        $this->singleRootCategory = (bool)$this->fixtureModel->getValue('assign_entities_to_all_websites', false);
        if ($this->websitesCount <= self::DEFAULT_WEBSITE_COUNT
            && $this->storeGroupsCount <= self::DEFAULT_STORE_COUNT
            && $this->storesCount <= self::DEFAULT_STORE_VIEW_COUNT
        ) {
            return;
        }
        //Get existing entities counts
        $storeGroups = $this->storeManager->getGroups();
        $this->storeGroupsIds= array_keys($storeGroups);
        foreach ($storeGroups as $storeGroupId => $storeGroup) {
            $this->storeGroupsToWebsites[$storeGroupId] = $storeGroup->getWebsiteId();
        }
        $this->websiteIds = array_values(array_unique($this->storeGroupsToWebsites));
        $this->defaultWebsite = $this->storeManager->getWebsite();
        $this->defaultStoreGroup = $this->storeManager->getGroup();
        $this->defaultWebsiteId = $this->defaultWebsite->getId();
        $this->defaultStoreGroupId = $this->defaultStoreGroup->getId();
        $this->defaultStoreView = $this->storeManager->getDefaultStoreView();
        $this->storeViewIds = array_keys($this->storeManager->getStores());
        $this->generateWebsites();
        $this->generateStoreGroups();
        $this->generateStoreViews();
    }

    /**
     * Generating web sites
     *
     * @return void
     */
    private function generateWebsites()
    {
        $existedWebsitesCount = count($this->websiteIds) + self::DEFAULT_WEBSITE_COUNT;
        while ($existedWebsitesCount <= $this->websitesCount) {
            $website = clone $this->defaultWebsite;
            $websiteCode = sprintf('website_%d', $existedWebsitesCount);
            $websiteName = sprintf('Website %d', $existedWebsitesCount);
            $website->addData(
                [
                    'website_id' => null,
                    'code' => $websiteCode,
                    'name' => $websiteName,
                    'is_default' => false,
                ]
            );
            $website->save();
            $this->websiteIds[] = $website->getId();
            $existedWebsitesCount++;
            $this->websiteCodes[] = $websiteCode;
        }
    }

    /**
     * Generating store groups ('stores' on frontend)
     *
     * @return void
     */
    private function generateStoreGroups()
    {
        $existedStoreGroupCount = count($this->storeGroupsIds);
        $existedWebsitesCount = count($this->websiteIds);
        while ($existedStoreGroupCount < $this->storeGroupsCount) {
            $websiteId = $this->websiteIds[$existedStoreGroupCount % $existedWebsitesCount];
            $storeGroupName = sprintf('Store Group %d - website_id_%d', ++$existedStoreGroupCount, $websiteId);
            $storeGroupCode = sprintf('store_group_%d', $existedStoreGroupCount);
            $storeGroup = clone $this->defaultStoreGroup;
            $storeGroup->addData(
                [
                    'group_id' => null,
                    'website_id' => $websiteId,
                    'name' => $storeGroupName,
                    'code' => $storeGroupCode,
                    'root_category_id' => $this->getStoreCategoryId($storeGroupName),
                ]
            );
            $storeGroup->save();
            $this->storeGroupsIds[] = $storeGroup->getId();
            $this->storeGroupsToWebsites[$storeGroup->getId()] = $websiteId;
        }
    }

    /**
     * Generating store views
     *
     * @return void
     */
    private function generateStoreViews()
    {
        $localesList = $this->localeConfig->getAllowedLocales();
        $localesListCount = count($localesList);
        $existedStoreViewsCount = count($this->storeViewIds);
        $existedStoreGroupCount = count($this->storeGroupsIds);
        while ($existedStoreViewsCount < $this->storesCount) {
            $groupId = $this->storeGroupsIds[$existedStoreViewsCount % $existedStoreGroupCount];
            $websiteId = $this->storeGroupsToWebsites[$groupId];
            $store = clone $this->defaultStoreView;
            $storeCode = sprintf('store_view_%d', ++$existedStoreViewsCount);
            $storeName = sprintf(
                'Store view %d - website_id_%d - group_id_%d',
                $existedStoreViewsCount,
                $websiteId,
                $groupId
            );
            $store->addData(
                [
                    'store_id' => null,
                    'name' => $storeName,
                    'website_id' => $websiteId,
                    'group_id' => $groupId,
                    'code' => $storeCode
                ]
            )->save();
            $this->saveStoreLocale($store->getId(), $localesList[$existedStoreViewsCount % $localesListCount]);
        }
    }

    /**
     * Saves store into locale
     *
     * @param int $storeId
     * @param string $localeCode
     * @return void
     */
    private function saveStoreLocale($storeId, $localeCode)
    {
        $this->scopeConfig->save(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            $localeCode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * Getting category id for store
     *
     * @param string $storeGroupName
     * @return int
     */
    private function getStoreCategoryId($storeGroupName)
    {
        if ($this->singleRootCategory) {
            return $this->getDefaultCategoryId();
        } else {
            //Generating category for store
            $category = $this->categoryFactory->create();
            $categoryPath = Category::TREE_ROOT_ID;
            $category->setName("Category " . $storeGroupName)
                ->setPath($categoryPath)
                ->setLevel(1)
                ->setAvailableSortBy('name')
                ->setDefaultSortBy('name')
                ->setIsActive(true)
                ->save();
            return $category->getId();
        }
    }

    /**
     * @inheritdoc
     */
    public function getActionTitle()
    {
        return 'Generating websites, stores and store views';
    }

    /**
     * @inheritdoc
     */
    public function introduceParamLabels()
    {
        return [
            'websites' => 'Websites',
            'store_groups' => 'Store Groups Count',
            'store_views' => 'Store Views Count'
        ];
    }

    /**
     * Get default category id
     *
     * @return int
     */
    private function getDefaultCategoryId()
    {
        if (null === $this->defaultParentCategoryId) {
            $this->defaultParentCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        }
        return $this->defaultParentCategoryId;
    }

    /**
     * Gets the website codes that were created by this object
     *
     * @return string[]
     */
    public function getWebsiteCodes()
    {
        return $this->websiteCodes;
    }
}
