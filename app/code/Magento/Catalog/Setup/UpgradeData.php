<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Category;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0.2') < 0) {
            $newBackendModel = 'Magento\Catalog\Model\Attribute\Backend\Startdate';
            $connection = $setup->getConnection();
            $connection->startSetup();
            $connection->update(
                $setup->getTable('eav_attribute'),
                ['backend_model' => $newBackendModel],
                ['backend_model = ?' => 'Magento\Catalog\Model\Product\Attribute\Backend\Startdate']
            );
            /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $attribute */
            foreach ($this->category->getAttributes() as $attribute) {
                if ($attribute->getAttributeCode() == 'custom_design_from') {
                    $attribute->setBackendModel($newBackendModel);
                    $attribute->save();
                    break;
                }
            }
            $connection->endSetup();
        }
    }
}
