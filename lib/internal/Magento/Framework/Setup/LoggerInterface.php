<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup;

/**
 * Interface to Log Message in Setup
 *
 * @api
 * @deprecated Replaced with ConsoleLogger
 * @see Magento\Framework\Setup\ConsoleLogger
 *
 * @since 100.0.2
 */
interface LoggerInterface
{
    /**
     * Logs success message
     *
     * @param string $message
     * @return void
     */
    public function logSuccess($message);

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
    public function log($message);

    /**
     * Logs a message in the current line
     *
     * @param string $message
     * @return void
     */
    public function logInline($message);

    /**
     * Logs meta information
     *
     * @param string $message
     * @return void
     */
    public function logMeta($message);
}
