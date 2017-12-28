<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

class WysiwygDefaultConfig implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig($config)
    {
        return $config;
    }
}
