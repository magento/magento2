<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

/**
 * Class \Magento\Framework\Message\ExceptionMessageFactoryPool
 *
 * @since 2.2.0
 */
class ExceptionMessageFactoryPool
{
    /**
     * Instances of factories that are specific for certain exceptions
     *
     * @var ExceptionMessageFactoryInterface[]
     * @since 2.2.0
     */
    private $exceptionMessageFactoryMap = [];

    /**
     * Default exception factory
     *
     * @var ExceptionMessageFactoryInterface
     * @since 2.2.0
     */
    private $defaultExceptionMessageFactory;

    /**
     * @param ExceptionMessageFactoryInterface $defaultExceptionMessageFactory
     * @param ExceptionMessageFactoryInterface[] $exceptionMessageFactoryMap
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getMessageFactory(\Exception $exception)
    {
        if (isset($this->exceptionMessageFactoryMap[get_class($exception)])) {
            return $this->exceptionMessageFactoryMap[get_class($exception)];
        }
        return $this->defaultExceptionMessageFactory;
    }
}
