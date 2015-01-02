<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
