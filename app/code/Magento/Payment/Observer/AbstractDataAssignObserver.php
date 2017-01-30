<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;

abstract class AbstractDataAssignObserver implements ObserverInterface
{
    const METHOD_CODE = 'method';

    const DATA_CODE = 'data';

    const MODEL_CODE = 'payment_model';

    /**
     * Reads method argument
     *
     * @param Observer $observer
     * @return MethodInterface
     */
    protected function readMethodArgument(Observer $observer)
    {
        return $this->readArgument($observer, static::METHOD_CODE, MethodInterface::class);
    }

    /**
     * Reads data argument
     *
     * @param Observer $observer
     * @return DataObject
     */
    protected function readDataArgument(Observer $observer)
    {
        return $this->readArgument($observer, static::DATA_CODE, DataObject::class);
    }

    /**
     * Reads argument of certain type
     *
     * @param Observer $observer
     * @param string $key
     * @param string $type
     * @return mixed
     * @throws \LogicException
     */
    protected function readArgument(Observer $observer, $key, $type)
    {
        $event = $observer->getEvent();
        $argument = $event->getDataByKey($key);

        if (!$argument instanceof $type) {
            throw new \LogicException('Wrong argument type provided.');
        }

        return $argument;
    }
}
