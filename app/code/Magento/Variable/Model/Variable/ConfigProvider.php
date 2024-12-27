<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Model\Variable;

use Magento\Framework\Data\Wysiwyg\ConfigProviderInterface;
use Magento\Framework\DataObject;

/**
 * Class ConfigProvider
 * This class has been added to prevent BIC changes in the \Magento\Variable\Model\Variable\Config
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * ConfigProvider constructor.
     * @param Config $variableConfig
     */
    public function __construct(
        private readonly Config $variableConfig
    ) {
    }

   /**
    * @inheritdoc
    */
    public function getConfig(DataObject $config): DataObject
    {
        $settings = $this->variableConfig->getWysiwygPluginSettings($config);
        return $config->addData($settings);
    }
}
