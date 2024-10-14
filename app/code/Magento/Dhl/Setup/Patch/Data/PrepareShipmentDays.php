<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Setup\Patch\Data;

use Magento\Framework\Console\Cli;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class PrepareShipmentDays implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * PrepareShipmentDays constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResolverInterface $localeResolver
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $days = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['dayNames']['format']['abbreviated'];

        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['config_id', 'value']
        )->where(
            'path = ?',
            'carriers/dhl/shipment_days'
        );
        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $configRow) {
            $row = [
                'value' => implode(
                    ',',
                    array_intersect_key(iterator_to_array($days), array_flip(explode(',', $configRow['value'] ?? '')))
                )
            ];
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                $row,
                ['config_id = ?' => $configRow['config_id']]
            );
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
