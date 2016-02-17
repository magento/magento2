<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Common interface for queue services in the cloud. This interface supports
 * most queue services and provides some flexibility for vendor-specific
 * features and requirements via an optional $options array in each method
 * signature. Classes implementing this interface should implement URI
 * construction for queues from the parameters given in each method and the
 * account data passed in to the constructor. Classes implementing this
 * interface are also responsible for security; access control isn't currently
 * supported in this interface, although we are considering access control
 * support in future versions of the interface.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Cloud_QueueService_Adapter
{
    /** Ctor HTTP adapter option */
    const HTTP_ADAPTER = 'http_adapter';

    /** Message visibility timeout option */
    const VISIBILITY_TIMEOUT = 'visibility_timeout';

    /** Default visibility timeout */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Create a queue. Returns the ID of the created queue (typically the URL).
     * It may take some time to create the queue. Check your vendor's
     * documentation for details.
     *
     * Name constraints: Maximum 80 characters
     *                      Only alphanumeric characters, hyphens (-), and underscores (_)
     *
     * @param  string $name
     * @param  array  $options
     * @return string Queue ID (typically URL)
     */
    public function createQueue($name, $options = null);

    /**
     * Delete a queue. All messages in the queue will also be deleted.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return boolean true if successful, false otherwise
     */
    public function deleteQueue($queueId, $options = null);

    /**
     * List all queues.
     *
     * @param  array $options
     * @return array Queue IDs
     */
    public function listQueues($options = null);

    /**
     * Get a key/value array of metadata for the given queue.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return array
     */
    public function fetchQueueMetadata($queueId, $options = null);

    /**
     * Store a key/value array of metadata for the specified queue.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath. Some adapters may not support this method.
     *
     * @param  string $queueId
     * @param  array  $metadata
     * @param  array  $options
     * @return void
     */
    public function storeQueueMetadata($queueId, $metadata,  $options = null);

    /**
     * Send a message to the specified queue.
     *
     * @param  string $queueId
     * @param  string $message
     * @param  array  $options
     * @return string Message ID
     */
    public function sendMessage($queueId, $message,  $options = null);

    /**
     * Recieve at most $max messages from the specified queue and return the
     * message IDs for messages recieved.
     *
     * @param  string $queueId
     * @param  int    $max
     * @param  array  $options
     * @return array[Zend_Cloud_QueueService_Message]  Array of messages
     */
    public function receiveMessages($queueId, $max = 1, $options = null);

    /**
     * Peek at the messages from the specified queue without removing them.
     *
     * @param  string $queueId
     * @param  int $num How many messages
     * @param  array  $options
     * @return array[Zend_Cloud_QueueService_Message]
     */
    public function peekMessages($queueId, $num = 1, $options = null);

    /**
     * Delete the specified message from the specified queue.
     *
     * @param  string $queueId
     * @param  Zend_Cloud_QueueService_Message $message Message to delete
     * @param  array  $options
     * @return void
     *
     */
    public function deleteMessage($queueId, $message,  $options = null);

    /**
     * Get the concrete adapter.
     */
    public function getClient();
}
