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
 * @since 100.0.2
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
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addMessages(array $messages, $group = null);

    /**
     * Adds new error message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 100.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addErrorMessage
     */
    public function addError($message, $group = null);

    /**
     * Adds new warning message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 100.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addWarningMessage
     */
    public function addWarning($message, $group = null);

    /**
     * Adds new notice message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 100.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addNoticeMessage
     */
    public function addNotice($message, $group = null);

    /**
     * Adds new success message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 100.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addSuccessMessage
     */
    public function addSuccess($message, $group = null);

    /**
     * Adds new error message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addErrorMessage($message, $group = null);

    /**
     * Adds new warning message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addWarningMessage($message, $group = null);

    /**
     * Adds new notice message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addNoticeMessage($message, $group = null);

    /**
     * Adds new success message
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addSuccessMessage($message, $group = null);

    /**
     * Adds new complex error message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexErrorMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex warning message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexWarningMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex notice message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexNoticeMessage($identifier, array $data = [], $group = null);

    /**
     * Adds new complex success message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexSuccessMessage($identifier, array $data = [], $group = null);

    /**
     * Adds messages array to message collection, without adding duplicate messages
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addUniqueMessages(array $messages, $group = null);

    /**
     * Adds a message describing an exception. Does not contain Exception handling logic.
     *
     * @param \Exception $exception
     * @param string|null $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     * @deprecated 100.1.0
     * @see \Magento\Framework\Message\ManagerInterface::addExceptionMessage
     */
    public function addException(\Exception $exception, $alternativeText = null, $group = null);

    /**
     * Adds a message describing an exception. Does not contain Exception handling logic.
     *
     * @param \Exception $exception
     * @param string|null $alternativeText
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText = null, $group = null);

    /**
     * Creates identified message
     *
     * @param string $type
     * @param string|null $identifier
     * @return MessageInterface
     * @throws \InvalidArgumentException
     */
    public function createMessage($type, $identifier = null);
}
