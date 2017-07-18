<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Plugin;

use Magento\Framework\Cache\FrontendInterface as FrontendCacheInterface;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Validation of DB up to date state
 */
class DbStatusValidator
{
    const PARAM_IGNORE_MODULE_VERSION_EXCEPTION = 'ignore_module_version_exception';

    /**
     * @var FrontendCacheInterface
     */
    private $cache;

    /**
     * @var DbVersionInfo
     */
    private $dbVersionInfo;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param FrontendCacheInterface $cache
     * @param DbVersionInfo $dbVersionInfo,
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        FrontendCacheInterface $cache,
        DbVersionInfo $dbVersionInfo,
        DeploymentConfig $deploymentConfig
    ) {
        $this->cache = $cache;
        $this->dbVersionInfo = $dbVersionInfo;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Perform check if DB is up to date
     *
     * @param FrontController $subject
     * @param RequestInterface $request
     * @return void
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        if ($this->deploymentConfig->get(self::PARAM_IGNORE_MODULE_VERSION_EXCEPTION, 0) === 0 &&
            !$this->cache->load('db_is_up_to_date')
        ) {
            $errors = $this->dbVersionInfo->getDbVersionErrors();

            if ($errors) {
                $message = 'Please upgrade your database: '
                           . "Run \"bin/magento setup:upgrade\" from the Magento root directory.\n"
                           . "The following modules are outdated:\n%1";

                throw new LocalizedException(new Phrase($message, [implode("\n", $this->formatErrors($errors))]));
            } else {
                $this->cache->save('true', 'db_is_up_to_date');
            }
        }
    }

    /**
     * Format each error in the error data from getOutOfDataDbErrors into a single message
     *
     * @param array $errorsData array of error data from getOutOfDateDbErrors
     * @return array Messages that can be used to log the error
     */
    private function formatErrors($errorsData)
    {
        $formattedErrors = [];

        foreach ($errorsData as $error) {
            $formattedErrors[] = $error[DbVersionInfo::KEY_MODULE] . ' ' . $error[DbVersionInfo::KEY_TYPE]
                                 . ': current version - ' . $error[DbVersionInfo::KEY_CURRENT]
                                 . ', required version - ' . $error[DbVersionInfo::KEY_REQUIRED];
        }

        return $formattedErrors;
    }
}
