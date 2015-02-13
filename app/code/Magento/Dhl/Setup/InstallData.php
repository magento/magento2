<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Setup;

use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataResourceInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * @param ListsInterface $localeLists
     */
    public function __construct(ListsInterface $localeLists)
    {
        $this->localeLists = $localeLists;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context)
    {
        $days = $this->localeLists->getTranslationList('days');

        $days = array_keys($days['format']['wide']);
        foreach ($days as $key => $value) {
            $days[$key] = ucfirst($value);
        }

        $select = $setup->getConnection()->select()->from(
            $setup->getTable('core_config_data'),
            ['config_id', 'value']
        )->where(
            'path = ?',
            'carriers/dhl/shipment_days'
        );

        foreach ($setup->getConnection()->fetchAll($select) as $configRow) {
            $row = ['value' => implode(',', array_intersect_key($days, array_flip(explode(',', $configRow['value']))))];
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                $row,
                ['config_id = ?' => $configRow['config_id']]
            );
        }
    }
}
