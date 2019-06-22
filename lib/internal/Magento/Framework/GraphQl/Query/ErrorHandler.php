<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use Magento\Framework\Logger\Monolog;

/**
 * Class ErrorHandler
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var Monolog
     */
    private $clientLogger;

    /**
     * @var Monolog
     */
    private $serverLogger;

    /**
     * @var array
     */
    private $clientErrorCategories;

    /**
     * @var array
     */
    private $serverErrorCategories;

    /**
     * @var Monolog
     */
    private $generalLogger;

    /**
     * ErrorHandler constructor.
     *
     * @param Monolog $clientLogger
     * @param Monolog $serverLogger
     * @param Monolog $generalLogger
     * @param array $clientErrorCategories
     * @param array $serverErrorCategories
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Monolog $clientLogger,
        Monolog $serverLogger,
        Monolog $generalLogger,
        array $clientErrorCategories = [],
        array $serverErrorCategories = []
    ) {
        $this->clientLogger = $clientLogger;
        $this->serverLogger = $serverLogger;
        $this->generalLogger = $generalLogger;
        $this->clientErrorCategories = $clientErrorCategories;
        $this->serverErrorCategories = $serverErrorCategories;
    }

    /**
     * Handle errors
     *
     * @param Error[] $errors
     * @param callable $formatter
     *
     * @return array
     */
    public function handle(array $errors, callable $formatter): array
    {
        return array_map(
            function (ClientAware $error) use ($formatter) {
                $this->logError($error);

                return $formatter($error);
            },
            $errors
        );
    }

    /**
     * @param ClientAware $error
     *
     * @return boolean
     */
    private function logError(ClientAware $error): bool
    {
        if (in_array($error->getCategory(), $this->clientErrorCategories)) {
            return $this->clientLogger->error($error);
        } elseif (in_array($error->getCategory(), $this->serverErrorCategories)) {
            return $this->serverLogger->error($error);
        }

        return $this->generalLogger->error($error);
    }
}
