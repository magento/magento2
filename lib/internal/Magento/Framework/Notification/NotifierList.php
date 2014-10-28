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

namespace Magento\Framework\Notification;

/*
 * List of registered system notifiers
 */
class NotifierList
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * List of notifiers
     *
     * @var NotifierInterface[]|string[]
     */
    protected $notifiers;

    /**
     * Whether the list of notifiers is verified (all notifiers should implement NotifierInterface  interface)
     *
     * @var bool
     */
    protected $isNotifiersVerified;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param NotifierInterface[]|string[] $notifiers
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, $notifiers = array())
    {
        $this->objectManager = $objectManager;
        $this->notifiers = $notifiers;
        $this->isNotifiersVerified = false;
    }

    /**
     * Returning list of notifiers.
     *
     * @return NotifierInterface[]
     * @throws \InvalidArgumentException
     */
    public function asArray()
    {
        if (!$this->isNotifiersVerified) {
            $hasErrors = false;
            foreach ($this->notifiers as $classIndex => $class) {
                $notifier = $this->objectManager->get($class);
                if ($notifier instanceof NotifierInterface) {
                    $this->notifiers[$classIndex] = $notifier;
                } else {
                    $hasErrors = true;
                    unset($this->notifiers[$classIndex]);
                }
            }
            $this->isNotifiersVerified = true;
            if ($hasErrors) {
                throw new \InvalidArgumentException('All notifiers should implements NotifierInterface');
            }
        }
        return $this->notifiers;
    }
}
