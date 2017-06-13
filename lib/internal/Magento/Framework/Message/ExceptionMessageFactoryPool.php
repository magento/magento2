<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

class ExceptionMessageFactoryPool
{
    /**
     * Key of instance is the exception format parameter
     *
     * @var ExceptionMessageFactoryInterface[]
     */
    private $exceptionMessageFactoryMap = [];

    /**
     * @param ExceptionMessageFactoryInterface $defaultExceptionMessageFactory
     * @param ExceptionMessageFactoryInterface[] $exceptionMessageFactoryMap
     */
    public function __construct(
        ExceptionMessageFactoryInterface $defaultExceptionMessageFactory,
        array $exceptionMessageFactoryMap = []
    ) {
        $this->defaultConfiguration = $defaultExceptionMessageFactory;
        $this->exceptionMessageFactoryMap = $exceptionMessageFactoryMap;
    }

    /**
     * Gets instance of a exception message factory
     *
     * @param \Exception $exception
     * @return ExceptionMessageFactoryInterface|null
     */
    public function getMessageGenerator(\Exception $exception)
    {
        if (isset($this->exceptionMessageFactoryMap[get_class($exception)])) {
            return $this->exceptionMessageFactoryMap[get_class($exception)];
        }
        return $this->defaultConfiguration;
    }
}
