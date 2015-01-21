<?php
/**
 * Validation of DB up to date state
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @throws \Magento\Framework\Module\Exception
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
                throw new \Magento\Framework\Module\Exception(
                    'Please update your database: Run "php -f index.php update" from the Magento root/setup directory.'
                    . PHP_EOL . 'The following modules are outdated:' . PHP_EOL . implode(PHP_EOL, $formattedErrors)
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
