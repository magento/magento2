<?php
/**
 * Created by PhpStorm.
 * User: ilagno
 * Date: 12/21/17
 * Time: 8:14 AM
 */

namespace Magento\Cms\Model;

class WysiwygDefaultConfig implements \Magento\Config\Model\Wysiwyg\ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig($config)
    {
        return $config;
    }
}