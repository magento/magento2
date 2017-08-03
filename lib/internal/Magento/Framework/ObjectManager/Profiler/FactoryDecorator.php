<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Profiler;

/**
 * Class \Magento\Framework\ObjectManager\Profiler\FactoryDecorator
 *
 * @since 2.0.0
 */
class FactoryDecorator implements \Magento\Framework\ObjectManager\FactoryInterface
{
    /**
     * Name of the class that generates logging wrappers
     */
    const GENERATOR_NAME = \Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger::class;

    /**
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     * @since 2.0.0
     */
    protected $subject;

    /**
     * @var Log
     * @since 2.0.0
     */
    protected $log;

    /**
     * @param \Magento\Framework\ObjectManager\FactoryInterface $subject
     * @param Log $log
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setObjectManager(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->subject->setObjectManager($objectManager);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
