<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Adds different types of messages to the session, and allows access to existing messages.
 *
 * @api
 * @since 2.0.0
 */
interface ManagerInterface
{
    /**
     * Retrieve messages
     *
     * @param bool $clear
     * @param string|null $group
     * @return Collection
     * @since 2.0.0
     */
    public function getMessages($clear = false, $group = null);

    /**
     * Retrieve default message group
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultGroup();

    /**
     * Adds new message to message collection
     *
     * @param MessageInterface $message
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addMessage(MessageInterface $message, $group = null);

    /**
     * Adds messages array to message collection
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addMessages(array $messages, $group = null);

    /**
     * Adds new error message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 2.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addErrorMessage
     * @since 2.0.0
     */
    public function addError($message, $group = null);

    /**
     * Adds new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 2.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addWarningMessage
     * @since 2.0.0
     */
    public function addWarning($message, $group = null);

    /**
     * Adds new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 2.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addNoticeMessage
     * @since 2.0.0
     */
    public function addNotice($message, $group = null);

    /**
     * Adds new success message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 2.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addSuccessMessage
     * @since 2.0.0
     */
    public function addSuccess($message, $group = null);

    /**
     * Adds new error message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addErrorMessage($message, $group = null);

    /**
     * Adds new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addWarningMessage($message, $group = null);

    /**
     * Adds new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addNoticeMessage($message, $group = null);

    /**
     * Adds new success message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addSuccessMessage($message, $group = null);

    /**
     * Adds new complex error message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addComplexErrorMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex warning message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addComplexWarningMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex notice message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addComplexNoticeMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex success message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addComplexSuccessMessage($identifier, array $data = [], $group = null);

    /**
     * Adds messages array to message collection, without adding duplicate messages
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addUniqueMessages(array $messages, $group = null);

    /**
     * Adds a message describing an exception. Does not contain Exception handling logic.
     *
     * @param \Exception $exception
     * @param string|null $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 2.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addExceptionMessage
     * @since 2.0.0
     */
    public function addException(\Exception $exception, $alternativeText = null, $group = null);

    /**
     * Adds a message describing an exception. Does not contain Exception handling logic.
     *
     * @param \Exception $exception
     * @param string|null $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     * @since 2.0.0
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText = null, $group = null);

    /**
     * Creates identified message
     *
     * @param string $type
     * @param string|null $identifier
     * @return MessageInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function createMessage($type, $identifier = null);
}
