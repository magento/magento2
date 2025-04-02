<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-02 09:25:29
 */

namespace Diepxuan\Magento\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class CreateCategorySimbaAttribute implements DataPatchInterface, PatchVersionInterface
{
    private $eavSetupFactory;
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        // $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if ($eavSetup->getAttribute(Category::ENTITY, 'simba_category')) {
            $eavSetup->removeAttribute(Category::ENTITY, 'simba_category');
        }
        $eavSetup->addAttribute(
            Category::ENTITY,
            'simba_category',
            [
                'type'       => 'varchar',
                'label'      => 'Category Simba',
                'input'      => 'text',
                'required'   => false,
                'sort_order' => 20,
                'global'     => ScopedAttributeInterface::SCOPE_STORE,
                'group'      => 'General Information',
            ]
        );

        return $this;
        // $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return '1.0.2';
    }

    public function getAliases()
    {
        return [];
    }
}
