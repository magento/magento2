<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification;

/*
 * List of registered system notifiers
 * @api
 */
class NotifierList
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param NotifierInterface[]|string[] $notifiers
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $notifiers = [])
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
                throw new \InvalidArgumentException('All notifiers should implement NotifierInterface');
            }
        }
        return $this->notifiers;
    }
}
