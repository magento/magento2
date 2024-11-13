<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Plugin;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Cache\FrontendInterface as FrontendCacheInterface;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Validation of DB up to date state
 */
class DbStatusValidator
{
    private const DEPLOYMENT_BLUE_GREEN_ENABLED = 'deployment/blue_green/enabled';

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
     * @param DbVersionInfo $dbVersionInfo
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
        if ($this->deploymentConfig->get(self::DEPLOYMENT_BLUE_GREEN_ENABLED)) {
            return;
        }

        if (!$this->cache->load('db_is_up_to_date')) {
            list($versionTooLowErrors, $versionTooHighErrors) = array_values($this->getGroupedDbVersionErrors());
            if ($versionTooHighErrors) {
                $message = 'Please update your modules: '
                    . "Run \"composer install\" from the Magento root directory.\n"
                    . "The following modules are outdated:\n%1";
                throw new LocalizedException(
                    new Phrase($message, [implode("\n", $this->formatVersionTooHighErrors($versionTooHighErrors))])
                );
            } elseif ($versionTooLowErrors) {
                $message = 'Please upgrade your database: '
                    . "Run \"bin/magento setup:upgrade\" from the Magento root directory.\n"
                    . "The following modules are outdated:\n%1";

                throw new LocalizedException(
                    new Phrase($message, [implode("\n", $this->formatVersionTooLowErrors($versionTooLowErrors))])
                );
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
    private function formatVersionTooLowErrors($errorsData)
    {
        $formattedErrors = [];

        foreach ($errorsData as $error) {
            $formattedErrors[] = $error[DbVersionInfo::KEY_MODULE] . ' ' . $error[DbVersionInfo::KEY_TYPE]
                . ': current version - ' . $error[DbVersionInfo::KEY_CURRENT]
                . ', required version - ' . $error[DbVersionInfo::KEY_REQUIRED];
        }

        return $formattedErrors;
    }

    /**
     * Format each error in the error data from getOutOfDataDbErrors into a single message
     *
     * @param array $errorsData array of error data from getOutOfDateDbErrors
     * @return array Messages that can be used to log the error
     */
    private function formatVersionTooHighErrors($errorsData)
    {
        $formattedErrors = [];
        foreach ($errorsData as $error) {
            $formattedErrors[] = $error[DbVersionInfo::KEY_MODULE] . ' ' . $error[DbVersionInfo::KEY_TYPE]
                . ': code version - ' . $error[DbVersionInfo::KEY_REQUIRED]
                . ', database version - ' . $error[DbVersionInfo::KEY_CURRENT];
        }

        return $formattedErrors;
    }

    /**
     * Return DB version errors grouped by 'version_too_low' and 'version_too_high'
     *
     * @return mixed
     */
    private function getGroupedDbVersionErrors()
    {
        $allDbVersionErrors = $this->dbVersionInfo->getDbVersionErrors();
        return array_reduce(
            (array)$allDbVersionErrors,
            function ($carry, $item) {
                if ($item[DbVersionInfo::KEY_CURRENT] === 'none'
                    || version_compare($item[DbVersionInfo::KEY_CURRENT], $item[DbVersionInfo::KEY_REQUIRED], '<')
                ) {
                    $carry['version_too_low'][] = $item;
                } else {
                    $carry['version_too_high'][] = $item;
                }
                return $carry;
            },
            [
                'version_too_low' => [],
                'version_too_high' => [],
            ]
        );
    }
}
