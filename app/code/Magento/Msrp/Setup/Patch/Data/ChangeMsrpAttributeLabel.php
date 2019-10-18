<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Change label for MSRP attribute.
 */
class ChangeMsrpAttributeLabel implements DataPatchInterface
{
    /**
     * @var CategorySetupFactory
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
     * @inheritdoc
     */
    public function apply()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $msrpAttribute = $categorySetup->getAttribute($entityTypeId, 'msrp');
        $categorySetup->updateAttribute(
            $entityTypeId,
            $msrpAttribute['attribute_id'],
            'frontend_label',
            'Minimum Advertised Price'
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeMsrpAttributes::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
