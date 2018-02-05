<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\Theme\Registration;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param Registration $themeRegistration
     */
    private $themeRegistration;

    /**
     * @param Registration $themeRegistration
     */
    public function __construct(Registration $themeRegistration)
    {
        $this->themeRegistration = $themeRegistration;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->themeRegistration->register();

    }

}
