<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\Theme\Registration;

/**
 * Register themes
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Registration
     * @since 2.1.0
     */
    private $themeRegistration;

    /**
     * Initialize dependencies
     *
     * @param Registration $themeRegistration
     * @since 2.0.0
     */
    public function __construct(Registration $themeRegistration)
    {
        $this->themeRegistration = $themeRegistration;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->themeRegistration->register();
    }
}
