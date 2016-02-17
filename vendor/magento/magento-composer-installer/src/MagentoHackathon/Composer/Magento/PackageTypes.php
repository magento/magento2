<?php

namespace MagentoHackathon\Composer\Magento;

/**
 * Class PackageTypes
 * @package MagentoHackathon\Composer\Magento
 */
class PackageTypes {

    /**
     * Package Types supported by Installer
     * @var array
     */
    public static $packageTypes = array(
        'magento2-module'   =>  '/app/code/',
        'magento2-theme'    =>  '/app/design/',
        'magento2-library'  =>  '/lib/internal/',
        'magento2-language' =>  '/app/i18n/',
        'magento2-component'=>  './',
    );
}
