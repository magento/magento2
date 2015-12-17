<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Category\Form\Element\Wysiwyg;

/**
 * Catalog Wysiwyg Config for Editor HTML Element
 */
class Config extends \Magento\Cms\Model\Wysiwyg\Config
{
    /**
     * {@inheritdoc}
     */
    function getConfig($data = [])
    {
        $config = parent::getConfig($data);
        $config->addData([
            'settings' => [
                'theme_advanced_buttons1' => 'bold,italic,|,justifyleft,justifycenter,justifyright,|,'
                    . 'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                'theme_advanced_buttons2' => null,
                'theme_advanced_buttons3' => null,
                'theme_advanced_buttons4' => null,
                'theme_advanced_statusbar_location' => null
            ],
            'files_browser_window_url' => false
        ]);

        return $config;
    }
}
