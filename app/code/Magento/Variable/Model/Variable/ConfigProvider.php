<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $settings = $this->variableConfig->getWysiwygPluginSettings($config);
        return $config->addData($settings);
    }
}
