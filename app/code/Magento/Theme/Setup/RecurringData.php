<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $this->themeRegistration->register();
    }
}
