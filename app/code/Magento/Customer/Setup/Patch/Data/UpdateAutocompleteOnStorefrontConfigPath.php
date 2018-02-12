<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateAutocompleteOnStorefrontCOnfigPath
 * @package Magento\Customer\Setup\Patch
 */
class UpdateAutocompleteOnStorefrontConfigPath implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpdateAutocompleteOnStorefrontCOnfigPath constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getConnection()->getTableName('core_config_data'),
            ['path' => \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE],
            ['path = ?' => 'general/restriction/autocomplete_on_storefront']
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddSecurityTrackingAttributes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.8';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
