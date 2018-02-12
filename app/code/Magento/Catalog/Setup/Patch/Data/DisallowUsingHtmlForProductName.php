<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class DisallowUsingHtmlForProductName.
 *
 * @package Magento\Catalog\Setup\Patch
 */
class DisallowUsingHtmlForProductName implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * DisallowUsingHtmlForProductName constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'name');

        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getConnection()->getTableName('catalog_eav_attribute'),
            ['is_html_allowed_on_front' => 0],
            $this->resourceConnection->getConnection()->quoteInto('attribute_id = ?', $attribute['attribute_id'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            ChangePriceAttributeDefaultScope::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.1.5';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
