<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateDefaultAttributeValue
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateDefaultAttributeValue implements DataPatchInterface, PatchVersionInterface
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
     * PatchInitial constructor.
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
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $categorySetup->updateAttribute(3, 54, 'default_value', 1);

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            SetNewResourceModelsPaths::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
