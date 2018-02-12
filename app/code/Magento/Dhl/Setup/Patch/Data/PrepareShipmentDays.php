<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Setup\Patch\Data;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class PrepareShipmentDays
 * @package Magento\Dhl\Setup\Patch
 */
class PrepareShipmentDays implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * PrepareShipmentDays constructor.
     * @param ResourceConnection $resourceConnection
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $days = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['dayNames']['format']['abbreviated'];

        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getConnection()->getTableName('core_config_data'),
            ['config_id', 'value']
        )->where(
            'path = ?',
            'carriers/dhl/shipment_days'
        );
        foreach ($this->resourceConnection->getConnection()->fetchAll($select) as $configRow) {
            $row = [
                'value' => implode(
                    ',',
                    array_intersect_key(iterator_to_array($days), array_flip(explode(',', $configRow['value'])))
                )
            ];
            $this->resourceConnection->getConnection()->update(
                $this->resourceConnection->getConnection()->getTableName('core_config_data'),
                $row,
                ['config_id = ?' => $configRow['config_id']]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
