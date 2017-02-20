<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig\Plugin;

use Magento\Framework\App\DeploymentConfig\ConfigHashManager;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Checks that deployment configuration hash is valid.
 * If hash is not valid throws LocalizedException.
 */
class ConfigHashValidator
{
    /**
     * The manager of deployment configuration hash.
     *
     * @var ConfigHashManager
     */
    private $configHashManager;

    /**
     * @param ConfigHashManager $configHashManager the manager of deployment configuration hash
     */
    public function __construct(ConfigHashManager $configHashManager)
    {
        $this->configHashManager = $configHashManager;
    }

    /**
     * Performs check that deployment configuration hash is valid.
     *
     * @param FrontController $subject the object of controller is wrapped by this plugin
     * @param RequestInterface $request the object that contains request params
     * @return void
     * @throws LocalizedException is thrown if deployment configuration hash is not valid
     */
    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        if (!$this->configHashManager->isHashValid()) {
            throw new LocalizedException(
                new Phrase(
                    'A change in configuration has been detected.'
                        . ' Run config:sync or setup:upgrade command to synchronize configuration.'
                )
            );
        }
    }
}
