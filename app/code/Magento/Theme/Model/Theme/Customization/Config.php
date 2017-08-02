<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * {@inheritdoc}
 */
namespace Magento\Theme\Model\Theme\Customization;

/**
 * Class \Magento\Theme\Model\Theme\Customization\Config
 *
 * @since 2.0.0
 */
class Config implements \Magento\Framework\View\Design\Theme\Customization\ConfigInterface
{
    /**
     * XML path to definitions of customization services
     */
    const XML_PATH_CUSTOM_FILES = 'theme/customization';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFileTypes()
    {
        $types = [];
        $convertNode = $this->config->getValue(self::XML_PATH_CUSTOM_FILES, 'default');
        if ($convertNode) {
            foreach ($convertNode as $name => $value) {
                $types[$name] = $value;
            }
        }
        return $types;
    }
}
