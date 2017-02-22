<?php
/**
 * Validation of DB up to date state
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Module\Plugin;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Module\DbVersionInfo;

class DbStatusValidator
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var DbVersionInfo
     */
    private $dbVersionInfo;

    /**
     * @param FrontendInterface $cache
     * @param DbVersionInfo $dbVersionInfo
     */
    public function __construct(
        FrontendInterface $cache,
        DbVersionInfo $dbVersionInfo
    ) {
        $this->cache = $cache;
        $this->dbVersionInfo = $dbVersionInfo;
    }

    /**
     * @param \Magento\Framework\App\FrontController $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->cache->load('db_is_up_to_date')) {
            $errors = $this->dbVersionInfo->getDbVersionErrors();
            if ($errors) {
                $formattedErrors = $this->formatErrors($errors);
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Please upgrade your database: Run "bin/magento setup:upgrade" from the Magento root directory.'
                        . ' %1The following modules are outdated:%2%3',
                        [PHP_EOL, PHP_EOL, implode(PHP_EOL, $formattedErrors)]
                    )
                );
            } else {
                $this->cache->save('true', 'db_is_up_to_date');
            }
        }
        return $proceed($request);
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
            $formattedErrors[] = $error[DbVersionInfo::KEY_MODULE] .
                ' ' . $error[DbVersionInfo::KEY_TYPE] .
                ': current version - ' . $error[DbVersionInfo::KEY_CURRENT ] .
                ', required version - ' . $error[DbVersionInfo::KEY_REQUIRED];
        }
        return $formattedErrors;
    }
}
