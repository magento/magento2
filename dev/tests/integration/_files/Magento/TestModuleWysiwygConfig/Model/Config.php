<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleWysiwygConfig\Model;

class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * Configuration override for WYSIWYG height
     * @var string
     */
    const CONFIG_HEIGHT = 'something_else';

    /**
     * Configuration override for WYSIWYG content css
     * @var string
     */
    const CONFIG_CONTENT_CSS = 'something_else.css';

    /**
     * @inheritdoc
     */
    public function getConfig($config)
    {
        $config['height'] = self::CONFIG_HEIGHT;
        $config['content_css'] = self::CONFIG_CONTENT_CSS;

        return $config;
    }
}
