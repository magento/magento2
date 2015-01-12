<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Interface to Log Message in Setup
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
