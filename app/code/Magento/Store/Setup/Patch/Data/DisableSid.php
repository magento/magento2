<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use \Magento\Framework\App\Config\MutableScopeConfigInterface;

/**
 * Disable default frontend SID
 */
class DisableSid implements DataPatchInterface, PatchVersionInterface
{

    /**
     * Config path for flag whether use SID on frontend
     */
    const XML_PATH_USE_FRONTEND_SID = 'web/session/use_frontend_sid';

    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * scope type
     */
    const SCOPE_STORE = 'store';

    /**
     * Disable Sid constructor.
     *
     * @param MutableScopeConfigInterface $mutableScopeConfig
     */
    public function __construct(
        MutableScopeConfigInterface $mutableScopeConfig
    ) {
        $this->mutableScopeConfig = $mutableScopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->mutableScopeConfig->setValue(self::XML_PATH_USE_FRONTEND_SID, 0, self::SCOPE_STORE);
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
