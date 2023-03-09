<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * {@inheritdoc}
 */
namespace Magento\Theme\Model\Theme\Customization;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\Customization\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * XML path to definitions of customization services
     */
    const XML_PATH_CUSTOM_FILES = 'theme/customization';

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        protected readonly ScopeConfigInterface $config
    ) {
    }

    /**
     * {@inheritdoc}
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
