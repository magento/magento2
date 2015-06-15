<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute;

/**
 * Class FrontendType
 */
class FrontendType
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Return list of input types by frontend type
     *
     * @param string $inputType
     * @return string
     */
    public function getType($inputType)
    {
        return array_search($inputType, $this->config);
    }

    /**
     * Return frontend type by input type
     *
     * @param  string $frontendType
     * @return string[]
     */
    public function getInputs($frontendType)
    {
        if (isset($this->config[$frontendType]) && is_array($this->config[$frontendType])) {
            return array_values($this->config[$frontendType]);
        }
        return [$frontendType];
    }
}
