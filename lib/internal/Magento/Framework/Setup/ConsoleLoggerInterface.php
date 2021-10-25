<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup;

/**
 * Interface to Log Message in Setup
 *
 * @api
 */
interface ConsoleLoggerInterface
{
    /**
     * Logs success message
     *
     * @param string $message
     * @return void
     */
    public function logSuccess(string $message);

    /**
     * Logs error message
     *
     * @param \Exception $e
     * @return void
     */
    public function logError(\Exception $e);

    /**
     * Logs a message
     *
     * @param string $message
     * @return void
     */
    public function log(string $message);

    /**
     * Logs a message in the current line
     *
     * @param string $message
     * @return void
     */
    public function logInline(string $message);

    /**
     * Logs meta information
     *
     * @param string $message
     * @return void
     */
    public function logMeta(string $message);

    /**
     * Logs meta information in the current line
     *
     * @param string $message
     * @return void
     */
    public function logMetaInline(string $message);
}
