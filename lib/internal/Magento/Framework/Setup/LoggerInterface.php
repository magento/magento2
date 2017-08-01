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
 * @since 2.0.0
 */
interface LoggerInterface
{
    /**
     * Logs success message
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    public function logSuccess($message);

    /**
     * Logs error message
     *
     * @param \Exception $e
     * @return void
     * @since 2.0.0
     */
    public function logError(\Exception $e);

    /**
     * Logs a message
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    public function log($message);

    /**
     * Logs a message in the current line
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    public function logInline($message);

    /**
     * Logs meta information
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    public function logMeta($message);
}
