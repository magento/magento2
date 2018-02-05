<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Setup\Patch;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param ResolverInterface $localeResolver
     */
    private $localeResolver;

    /**
     * @param ResolverInterface $localeResolver
     */
    public function __construct(ResolverInterface $localeResolver)
    {
        $this->localeResolver = $localeResolver;
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
        $days = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['dayNames']['format']['abbreviated'];

        $select = $setup->getConnection()->select()->from(
            $setup->getTable('core_config_data'),
            ['config_id', 'value']
        )->where(
            'path = ?',
            'carriers/dhl/shipment_days'
        );
        foreach ($setup->getConnection()->fetchAll($select) as $configRow) {
            $row = [
                'value' => implode(
                    ',',
                    array_intersect_key(iterator_to_array($days), array_flip(explode(',', $configRow['value'])))
                )
            ];
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                $row,
                ['config_id = ?' => $configRow['config_id']]
            );
        }

    }

}
