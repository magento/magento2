<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;

/**
 * Class for retrieving configuration from initial config by website scope
 */
class Website implements SourceInterface
{
    /**
     * @var Initial
     */
    private $initialConfig;

    /**
     * @var DefaultScope
     */
    private $defaultScope;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param DefaultScope $defaultScope
     * @param Converter $converter
     */
    public function __construct(
        Initial $initialConfig,
        DefaultScope $defaultScope,
        Converter $converter
    ) {
        $this->initialConfig = $initialConfig;
        $this->defaultScope = $defaultScope;
        $this->converter = $converter;
    }

    /**
     * Retrieve config by website scope
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function get($scopeCode = null)
    {
        return $this->converter->convert(array_replace_recursive(
            $this->defaultScope->get(),
            $this->initialConfig->getData("websites|{$scopeCode}")
        ));
    }
}
