<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\Collector\CspWhitelistXml;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Config\Data\Scoped;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Config\CacheInterface;

/**
 * Provides CSP whitelist configuration
 */
class Data extends Scoped
{
    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = ['global'];

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param ScopeInterface $configScope
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Reader $reader,
        ScopeInterface $configScope,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        parent::__construct($reader, $configScope, $cache, 'csp_whitelist_config', $serializer);
    }
}
