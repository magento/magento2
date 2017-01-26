<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\App\State;

/**
 * Example Application State class.
 */
class State1 extends AbstractState
{
    /**
     * Data for configuration state.
     *
     * @var string
     */
    protected $config ='admin_session_lifetime_1_hour, wysiwyg_disabled, admin_account_sharing_enable, log_to_file';

    /**
     * Apply set up configuration profile.
     *
     * @return void
     */
    public function apply()
    {
        parent::apply();
        if (file_exists(dirname(dirname(dirname(MTF_BP))) . '/app/etc/config.php')) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->config]
            )->run();
        }
    }

    /**
     * Get name of the Application State Profile.
     *
     * @return string
     */
    public function getName()
    {
        return 'Configuration Profile #1';
    }
}
