<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch204
{


    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();


        $mediaBackendType = 'static';
        $mediaBackendModel = null;
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $categorySetup->updateAttribute(
            'catalog_product',
            'media_gallery',
            'backend_type',
            $mediaBackendType
        );
        $categorySetup->updateAttribute(
            'catalog_product',
            'media_gallery',
            'backend_model',
            $mediaBackendModel
        );


        $setup->endSetup();

    }

}
