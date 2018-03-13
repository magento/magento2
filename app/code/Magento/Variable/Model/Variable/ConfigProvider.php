<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Model\Variable;

/**
 * Class ConfigProvider
 * This class has been added to prevent BIC changes in the \Magento\Variable\Model\Variable\Config
 */
class ConfigProvider implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Variable\Model\Variable\Config
     */
    private $variableConfig;

    /**
     * ConfigProvider constructor.
     * @param Config $variableConfig
     */
    public function __construct(Config $variableConfig)
    {
        $this->variableConfig = $variableConfig;
    }

   /**
    * {@inheritdoc}
    *
    */
    public function getConfig($config)
    {
        $settings = $this->variableConfig->getWysiwygPluginSettings($config);
        return $config->addData($settings);
    }
}
