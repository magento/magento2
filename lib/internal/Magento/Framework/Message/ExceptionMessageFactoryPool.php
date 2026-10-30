<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Message;

class ExceptionMessageFactoryPool
{
    /**
     * Instances of factories that are specific for certain exceptions
     *
     * @var ExceptionMessageFactoryInterface[]
     */
    private $exceptionMessageFactoryMap = [];

    /**
     * Default exception factory
     *
     * @var ExceptionMessageFactoryInterface
     */
    private $defaultExceptionMessageFactory;

    /**
     * @param ExceptionMessageFactoryInterface $defaultExceptionMessageFactory
     * @param ExceptionMessageFactoryInterface[] $exceptionMessageFactoryMap
     */
    public function __construct(
        ExceptionMessageFactoryInterface $defaultExceptionMessageFactory,
        array $exceptionMessageFactoryMap = []
    ) {
        $this->defaultExceptionMessageFactory = $defaultExceptionMessageFactory;
        $this->exceptionMessageFactoryMap = $exceptionMessageFactoryMap;
    }

    /**
     * Gets instance of a exception message factory
     *
     * @param \Exception $exception
     * @return ExceptionMessageFactoryInterface|null
     */
    public function getMessageFactory(\Exception $exception)
    {
        if (isset($this->exceptionMessageFactoryMap[get_class($exception)])) {
            return $this->exceptionMessageFactoryMap[get_class($exception)];
        }
        return $this->defaultExceptionMessageFactory;
    }
}
