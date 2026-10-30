<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model;

class WysiwygDefaultConfig implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        return $config;
    }
}
