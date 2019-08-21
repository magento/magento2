<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Debug;

/**
 * Complex exception message manager model
 */
class ComplexExceptionManager extends Manager
{
    /**
     * @var ExceptionMessageFactoryInterface
     */
    private $exceptionMessageFactory;

    /**
     * ComplexExceptionManager constructor.
     *
     * @param ExceptionMessageFactoryInterface $exceptionMessageFactory
     */
    public function __construct(ExceptionMessageFactoryInterface $exceptionMessageFactory)
    {
        $this->exceptionMessageFactory = $exceptionMessageFactory;
    }

    /**
     * Adds a complex message describing an exception. Does not contain Exception handling logic
     *
     * @param string $identifier
     * @param Exception $exception
     * @param bool $displayAlternativeText
     * @param array $data
     * @param string $group
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function addComplexExceptionMessage(
        string $identifier,
        Exception $exception,
        $displayAlternativeText = false,
        array $data = [],
        $group = ''
    ) {
        $this->assertNotEmptyIdentifier($identifier);

        $message = sprintf(
            'Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            Debug::trace(
                $exception->getTrace(),
                true,
                true,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            )
        );

        $this->logger->critical($message);

        if ($displayAlternativeText) {
            $this->addComplexErrorMessage($identifier, $data, $group);
        } else {
            $this->addMessage($this->exceptionMessageFactory->createMessage($exception), $group);
        }

        return $this;
    }

    /**
     * Asserts that identifier is not empty
     *
     * @param mixed $identifier
     */
    private function assertNotEmptyIdentifier($identifier): void
    {
        if (empty($identifier)) {
            throw new InvalidArgumentException('Message identifier should not be empty');
        }
    }
}
