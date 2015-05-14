<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Adds different types of messages to the session, and allows access to existing messages.
 *
 * @api
 */
interface ManagerInterface
{
    /**
     * Retrieve messages
     *
     * @param bool $clear
     * @param string|null $group
     * @return Collection
     */
    public function getMessages($clear = false, $group = null);

    /**
     * Retrieve default message group
     *
     * @return string
     */
    public function getDefaultGroup();

    /**
     * Adds new message to message collection
     *
     * @param MessageInterface $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addMessage(MessageInterface $message, $group = null);

    /**
     * Adds messages array to message collection
     *
     * @param array $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addMessages(array $messages, $group = null);

    /**
     * Adds new error message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addError($message, $group = null);

    /**
     * Adds new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addWarning($message, $group = null);

    /**
     * Adds new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addNotice($message, $group = null);

    /**
     * Adds new success message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addSuccess($message, $group = null);

    /**
     * Adds messages array to message collection, without adding duplicate messages
     *
     * @param array|MessageInterface $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addUniqueMessages($messages, $group = null);

    /**
     * Adds a message describing an exception. Does not contain Exception handling logic.
     *
     * @param \Exception $exception
     * @param string $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addException(\Exception $exception, $alternativeText, $group = null);
}
