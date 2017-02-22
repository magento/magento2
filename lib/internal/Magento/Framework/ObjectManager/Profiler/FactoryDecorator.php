<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Profiler;

class FactoryDecorator implements \Magento\Framework\ObjectManager\FactoryInterface
{
    /**
     * Name of the class that generates logging wrappers
     */
    const GENERATOR_NAME = 'Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger';

    /**
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     */
    protected $subject;

    /**
     * @var Log
     */
    protected $log;

    /**
     * @param \Magento\Framework\ObjectManager\FactoryInterface $subject
     * @param Log $log
     */
    public function __construct(\Magento\Framework\ObjectManager\FactoryInterface $subject, Log $log)
    {
        $this->subject = $subject;
        $this->log = $log;
    }

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function setObjectManager(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->subject->setObjectManager($objectManager);
    }

    /**
     * {@inheritdoc}
     */
    public function create($requestedType, array $arguments = [])
    {
        $this->log->startCreating($requestedType);
        $result = $this->subject->create($requestedType, $arguments);
        if ($requestedType !== self::GENERATOR_NAME) {
            $loggerClassName = get_class($result) . "\\Logger";
            $wrappedResult = new $loggerClassName($result, $this->log);
            $this->log->stopCreating($result);
            $result = $wrappedResult;
        }
        return $result;
    }
}
