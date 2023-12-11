<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator;
use Psr\Log\LoggerInterface;

/**
 * Class loader and generator.
 */
class Autoloader
{
    /**
     * @var Generator
     */
    protected $_generator;

    /**
     * Enables guarding against spamming the debug log with duplicate messages, as
     * the generation exception will be thrown multiple times within a single request.
     *
     * @var string
     */
    private $lastGenerationErrorMessage;

    /**
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->_generator = $generator;
    }

    /**
     * Load specified class name and generate it if necessary
     *
     * According to PSR-4 section 2.4 an autoloader MUST NOT throw an exception and SHOULD NOT return a value.
     *
     * @see https://www.php-fig.org/psr/psr-4/
     *
     * @param string $className
     * @return void
     */
    public function load($className)
    {
        if (! class_exists($className)) {
            try {
                $this->_generator->generateClass($className);
            } catch (\Exception $exception) {
                $this->tryToLogExceptionMessageIfNotDuplicate($exception);
            }
        }
    }

    /**
     * Log exception.
     *
     * @param \Exception $exception
     */
    private function tryToLogExceptionMessageIfNotDuplicate(\Exception $exception): void
    {
        if ($this->lastGenerationErrorMessage !== $exception->getMessage()) {
            $this->lastGenerationErrorMessage = $exception->getMessage();
            $this->tryToLogException($exception);
        }
    }

    /**
     * Try to capture the exception message.
     *
     * The Autoloader is instantiated before the ObjectManager, so the LoggerInterface can not be injected.
     * The Logger is instantiated in the try/catch block because ObjectManager might still not be initialized.
     * In that case the exception message can not be captured.
     *
     * The debug level is used for logging in case class generation fails for a common class, but a custom
     * autoloader is used later in the stack. A more severe log level would fill the logs with messages on production.
     * The exception message now can be accessed in developer mode if debug logging is enabled.
     *
     * @param \Exception $exception
     * @return void
     */
    private function tryToLogException(\Exception $exception): void
    {
        try {
            $logger = ObjectManager::getInstance()->get(LoggerInterface::class);
            $logger->debug($exception->getMessage(), ['exception' => $exception]);
        } catch (\Exception $ignoreThisException) {
            // Do not take an action here, since the original exception might have been caused by logger
        }
    }
}
