<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\ResourceConnection;

/**
 * Website and category provider
 */
class WebsiteCategoryProvider
{
    /**
     * @var array
     */
    private $categoriesPerWebsite;

    /**
     * @var FixtureConfig
     */
    private $fixtureConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $websites;

    /**
     * @var array
     */
    private $categories;

    /**
     * @param FixtureConfig $fixtureConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        FixtureConfig $fixtureConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->fixtureConfig = $fixtureConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get websites for $productIndex product
     *
     * @param int $productIndex Index of generated product
     * @return array
     * @throws \Exception
     */
    public function getWebsiteIds($productIndex)
    {
        if ($this->isAssignToAllWebsites()) {
            return $this->getAllWebsites();
        } else {
            $categoriesPerWebsite = $this->getCategoriesAndWebsites();
            if (!count($categoriesPerWebsite)) {
                throw new \Exception('Cannot find categories. Please, be sure that you have generated categories');
            }
            return [$categoriesPerWebsite[$productIndex % count($categoriesPerWebsite)]['website']];
        }
    }

    /**
     * Get product if for $productIndex product
     *
     * @param int $productIndex
     * @return int
     */
    public function getCategoryId($productIndex)
    {
        if ($this->isAssignToAllWebsites()) {
            $categories = $this->getAllCategories();
            return $categories[$productIndex % count($categories)];
        } else {
            $categoriesPerWebsite = $this->getCategoriesAndWebsites();
            return $categoriesPerWebsite[$productIndex % count($categoriesPerWebsite)]['category'];
        }
    }

    /**
     * @return array
     */
    private function getCategoriesAndWebsites()
    {
        if (null === $this->categoriesPerWebsite) {
            $select = $this->getConnection()->select()
                ->from(
                    ['c' => $this->resourceConnection->getTableName('catalog_category_entity')],
                    ['category' => 'entity_id']
                )->join(
                    ['sg' => $this->resourceConnection->getTableName('store_group')],
                    "c.path like concat('1/', sg.root_category_id, '/%')",
                    ['website' => 'website_id']
                );
            $this->categoriesPerWebsite = $this->getConnection()->fetchAll($select);
        }

        return $this->categoriesPerWebsite;
    }

    /**
     * @return bool
     */
    private function isAssignToAllWebsites()
    {
        return (bool)$this->fixtureConfig->getValue('assign_entities_to_all_websites', false);
    }

    /**
     * @return array
     */
    private function getAllWebsites()
    {
        if (null === $this->websites) {
            $this->websites = array_unique(array_column($this->getCategoriesAndWebsites(), 'website'));
        }

        return $this->websites;
    }

    /**
     * @return array
     */
    private function getAllCategories()
    {
        if (null === $this->categories) {
            $this->categories = array_values(array_unique(array_column($this->getCategoriesAndWebsites(), 'category')));
        }

        return $this->categories;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
