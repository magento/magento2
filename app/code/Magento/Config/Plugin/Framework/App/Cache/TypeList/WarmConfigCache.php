<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Plugin\Framework\App\Cache\TypeList;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Cache\Type\Config as TypeConfig;
use Magento\Framework\App\Cache\TypeList;

/**
 * Plugin that for warms config cache when config cache is cleaned.
 * This is to reduce the lock time after flushing config cache.
 */
class WarmConfigCache
{
    /**
     * @var ConfigTypeInterface
     */
    private $system;

    /**
     * @param ConfigTypeInterface $system
     */
    public function __construct(ConfigTypeInterface $system)
    {
        $this->system = $system;
    }

    /**
     * Around plugin for cache's clean type method
     *
     * @param TypeList $subject
     * @param callable $proceed
     * @param string $typeCode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCleanType(TypeList $subject, callable $proceed, $typeCode)
    {
        if (TypeConfig::TYPE_IDENTIFIER !== $typeCode) {
            return $proceed($typeCode);
        }
        $cleaner = function () use ($proceed, $typeCode) {
            return $proceed($typeCode);
        };
        $this->system->cleanAndWarmDefaultScopeData($cleaner);
    }
}
