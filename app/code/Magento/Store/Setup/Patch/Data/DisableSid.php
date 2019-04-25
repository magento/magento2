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
 * Class CreateDefaultPages
 * @package Magento\Cms\Setup\Patch
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
     * @var string
     */
    protected $_scopeType;

    /**
     * Disable Sid constructor.
     * @param MutableScopeConfigInterface $mutableScopeConfig
     * @param string $scopeType
     */
    public function __construct(
        MutableScopeConfigInterface $mutableScopeConfig,
        $scopeType
    ) {
        $this->mutableScopeConfig=$mutableScopeConfig;
        $this->_scopeType=$scopeType;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $this->mutableScopeConfig->setValue(self::XML_PATH_USE_FRONTEND_SID, 0, $this->_scopeType);
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
