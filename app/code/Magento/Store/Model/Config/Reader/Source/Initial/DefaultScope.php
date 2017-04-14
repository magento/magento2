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
 */
class DefaultScope implements SourceInterface
{
    /**
     * @var Initial
     */
    private $initialConfig;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param Converter $converter
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
     */
    public function get($scopeCode = null)
    {
        return $this->converter->convert($this->initialConfig->getData(ScopeConfigInterface::SCOPE_TYPE_DEFAULT));
    }
}
