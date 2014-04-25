<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
