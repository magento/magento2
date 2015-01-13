<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Message manager interface
 */
interface ManagerInterface
{
    /**
     * Default message group
     */
    const DEFAULT_GROUP = 'default';

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
     * Adding new message to message collection
     *
     * @param MessageInterface $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addMessage(MessageInterface $message, $group = null);

    /**
     * Adding messages array to message collection
     *
     * @param array $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addMessages(array $messages, $group = null);

    /**
     * Adding new error message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addError($message, $group = null);

    /**
     * Adding new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addWarning($message, $group = null);

    /**
     * Adding new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addNotice($message, $group = null);

    /**
     * Adding new success message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addSuccess($message, $group = null);

    /**
     * Adds messages array to message collection, but doesn't add duplicates to it
     *
     * @param array|MessageInterface $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addUniqueMessages($messages, $group = null);

    /**
     * Not Magento exception handling
     *
     * @param \Exception $exception
     * @param string $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addException(\Exception $exception, $alternativeText, $group = null);
}
