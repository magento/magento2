<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup\Patch\Data;

use Magento\Theme\Model\Theme\Registration;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class RegisterThemes
 * @package Magento\Theme\Setup\Patch
 */
class RegisterThemes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var Registration
     */
    private $themeRegistration;

    /**
     * RegisterThemes constructor.
     * @param ResourceConnection $resourceConnection
     * @param Registration $themeRegistration
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Registration $themeRegistration
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->themeRegistration = $themeRegistration;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->themeRegistration->register();
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
    public static function getVersion()
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
