<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        if (version_compare($context->getVersion(), '2.1.3', '<')) {
            $this->changePriceAttributeDefaultScope($categorySetup, $entityTypeId);
        }
        $setup->endSetup();
    }

    /**
     * @param \Magento\Catalog\Setup\CategorySetup $categorySetup
     * @param int $entityTypeId
     * @return void
     */
    private function changePriceAttributeDefaultScope($categorySetup, $entityTypeId)
    {
        $attribute = $categorySetup->getAttribute($entityTypeId, 'msrp');
        $categorySetup->updateAttribute(
            $entityTypeId,
            $attribute['attribute_id'],
            'is_global',
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        );

    }
}
