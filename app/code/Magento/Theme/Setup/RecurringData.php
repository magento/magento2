<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\Theme\Registration;

/**
 * Upgrade registered themes
 */
class RecurringData implements InstallDataInterface
{
    /**
     * Theme registration
     *
     * @var Registration
     */
    private $themeRegistration;

    /**
     * Init
     *
     * @param Registration $themeRegistration
     */
    public function __construct(Registration $themeRegistration)
    {
        $this->themeRegistration = $themeRegistration;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $dbThemes = $this->themeRegistration->getAllDbThemes()->getData();
        $dbThemeNames = [];
        foreach ($dbThemes as $dbTheme) {
            $dbThemeNames[] = $dbTheme['area'] . '/' . $dbTheme['code'];
        }

        $filesystemThemes = $this->themeRegistration->getAllPhysicalThemes()->getItems();
        $filesystemThemeNames = array_keys($filesystemThemes);

        if (sizeof(array_diff($dbThemeNames, $filesystemThemeNames)) > 0
            || sizeof(array_diff($filesystemThemeNames, $dbThemeNames)) > 0) {
            $this->themeRegistration->register();
        }
    }
}
