<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for retrieving configuration from initial by default scope
 * @since 2.1.3
 */
class DefaultScope implements SourceInterface
{
    /**
     * @var Initial
     * @since 2.1.3
     */
    private $initialConfig;

    /**
     * @var Converter
     * @since 2.1.3
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param Converter $converter
     * @since 2.1.3
     */
    public function __construct(
        Initial $initialConfig,
        Converter $converter
    ) {
        $this->initialConfig = $initialConfig;
        $this->converter = $converter;
    }

    /**
     * Retrieve config by default scope
     *
     * @param string|null $scopeCode
     * @return array
     * @since 2.1.3
     */
    public function get($scopeCode = null)
    {
        return $this->converter->convert($this->initialConfig->getData(ScopeConfigInterface::SCOPE_TYPE_DEFAULT));
    }
}
