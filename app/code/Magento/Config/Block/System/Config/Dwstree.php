<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin customer left menu
 */
namespace Magento\Config\Block\System\Config;

class Dwstree extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('system_config_dwstree');
        $this->setDestElementId('system_config_form');
    }

    /**
     * @return $this
     */
    public function initTabs()
    {
        $section = $this->getRequest()->getParam('section');

        $curWebsite = $this->getRequest()->getParam('website');
        $curStore = $this->getRequest()->getParam('store');

        $this->addTab(
            'default',
            [
                'label' => __('Default Config'),
                'url' => $this->getUrl('*/*/*', ['section' => $section]),
                'class' => 'default'
            ]
        );

        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->_storeManager->getWebsites(true) as $website) {
            $wCode = $website->getCode();
            $wName = $website->getName();
            $wUrl = $this->getUrl('*/*/*', ['section' => $section, 'website' => $wCode]);
            $this->addTab('website_' . $wCode, ['label' => $wName, 'url' => $wUrl, 'class' => 'website']);
            if ($curWebsite === $wCode) {
                if ($curStore) {
                    $this->_addBreadcrumb($wName, '', $wUrl);
                } else {
                    $this->_addBreadcrumb($wName);
                }
            }
            /** @var $store \Magento\Store\Model\Store */
            foreach ($website->getStores() as $store) {
                $sCode = $store->getCode();
                $sName = $store->getName();
                $this->addTab(
                    'store_' . $sCode,
                    [
                        'label' => $sName,
                        'url' => $this->getUrl(
                            '*/*/*',
                            ['section' => $section, 'website' => $wCode, 'store' => $sCode]
                        ),
                        'class' => 'store'
                    ]
                );
                if ($curStore === $sCode) {
                    $this->_addBreadcrumb($sName);
                }
            }
        }
        if ($curStore) {
            $this->setActiveTab('store_' . $curStore);
        } elseif ($curWebsite) {
            $this->setActiveTab('website_' . $curWebsite);
        } else {
            $this->setActiveTab('default');
        }

        return $this;
    }
}
