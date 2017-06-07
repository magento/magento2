<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\App\State;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Example Application State class.
 */
class State1 extends AbstractState
{
    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Data for configuration state.
     *
     * @var string
     */
    protected $config ='admin_session_lifetime_1_hour, wysiwyg_disabled, admin_account_sharing_enable, log_to_file';

    /**
     * HTTP CURL Adapter.
     *
     * @var CurlTransport
     */
    private $curlTransport;

    /**
     * @param ObjectManager $objectManager
     * @param CurlTransport $curlTransport
     * @param array $arguments
     */
    public function __construct(
        ObjectManager $objectManager,
        CurlTransport $curlTransport,
        array $arguments = []
    ) {
        parent::__construct($objectManager, $arguments);
        $this->objectManager = $objectManager;
        $this->curlTransport = $curlTransport;
    }

    /**
     * Apply set up configuration profile.
     *
     * @return void
     */
    public function apply()
    {
        parent::apply();
        $this->curlTransport->write($_ENV['app_frontend_url'], [], CurlInterface::GET);
        $response = $this->curlTransport->read();
        if (strpos($response, 'Home Page') !== false) {
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
