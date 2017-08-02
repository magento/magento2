<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * Class AbstractDataAssignObserver
 * @package Magento\Payment\Observer
 * @api
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    protected function readMethodArgument(Observer $observer)
    {
        return $this->readArgument($observer, static::METHOD_CODE, MethodInterface::class);
    }

    /**
     * Reads payment model argument
     *
     * @param Observer $observer
     * @return InfoInterface
     * @since 2.1.0
     */
    protected function readPaymentModelArgument(Observer $observer)
    {
        return $this->readArgument($observer, static::MODEL_CODE, InfoInterface::class);
    }

    /**
     * Reads data argument
     *
     * @param Observer $observer
     * @return DataObject
     * @since 2.0.0
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
     * @since 2.0.0
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
