<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

/**
 * Log Configuration Provider
 *
 * Reads Log configuration from deployment (env.php) and instantiates the required
 * objects on demand.
 *
 * Configuration format is inspired by MonologCascade (https://github.com/theorchard/monolog-cascade)
 * Implementation is made specific for Magento (e.g. it uses the ObjectManager to instantiate objects allowing DI)
 */
class LogConfigurationProvider implements LogConfigurationProviderInterface
{
    const TYPE_FORMATTER = 'formatter';
    const TYPE_HANDLER = 'handler';
    const TYPE_PROCESSOR = 'processor';
    const TYPE_OTHER = 'other';

    /**
     * @var array
     */
    private $formattersByKey = [];

    /**
     * @var array
     */
    private $handlersByKey = [];

    /**
     * @var array
     */
    private $processorsByKey = [];

    /**
     * @var array
     */
    private $logConfiguration;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Utility\ObjectInstantiator
     */
    private $objectInstantiator;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Utility\ObjectInstantiator $objectInstantiator
     */
    public function __construct(DeploymentConfig $deploymentConfig, Utility\ObjectInstantiator $objectInstantiator)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->objectInstantiator = $objectInstantiator;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerByKey(string $key): HandlerInterface
    {
        if ($this->logConfiguration === null) {
            $this->logConfiguration = $this->deploymentConfig->get('logging');
        }

        return $this->getOrInstantiateHandlerByKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessorByKey(string $key)
    {
        if ($this->logConfiguration === null) {
            $this->logConfiguration = $this->deploymentConfig->get('logging');
        }

        return $this->getOrInstantiateProcessorByKey($key);
    }

    /**
     * Get or instantiate formatter by key
     *
     * @param string $key
     * @return FormatterInterface
     */
    private function getOrInstantiateFormatterByKey(string $key): FormatterInterface
    {
        if (isset($this->formattersByKey[$key])) {
            return $this->formattersByKey[$key];
        }

        if (!isset($this->logConfiguration['formatters']) || !isset($this->logConfiguration['formatters'][$key])) {
            throw new \RuntimeException('Log formatter with not found with key: ' . $key);
        }

        $this->formattersByKey[$key] = $this->instantiateItem(
            $this->logConfiguration['formatters'][$key],
            self::TYPE_FORMATTER
        );

        return $this->formattersByKey[$key];
    }

    /**
     * Get or instantiate processor by key
     *
     * @param string $key
     * @return mixed
     */
    private function getOrInstantiateProcessorByKey(string $key)
    {
        if (isset($this->processorsByKey[$key])) {
            return $this->processorsByKey[$key];
        }

        if (!isset($this->logConfiguration['processors']) || !isset($this->logConfiguration['processors'][$key])) {
            throw new \RuntimeException('Log processor not found with key: ' . $key);
        }

        $this->processorsByKey[$key] = $this->instantiateItem(
            $this->logConfiguration['processors'][$key],
            self::TYPE_PROCESSOR
        );

        return $this->processorsByKey[$key];
    }

    /**
     * Get or instantiate handler by key
     *
     * @param string $key
     * @return HandlerInterface
     */
    private function getOrInstantiateHandlerByKey(string $key): HandlerInterface
    {
        if (isset($this->handlersByKey[$key])) {
            return $this->handlersByKey[$key];
        }

        if (!isset($this->logConfiguration['handlers']) || !isset($this->logConfiguration['handlers'][$key])) {
            throw new \RuntimeException('Log handlers not found with key: ' . $key);
        }

        $this->handlersByKey[$key] = $this->instantiateItem(
            $this->logConfiguration['handlers'][$key],
            self::TYPE_HANDLER
        );

        return $this->handlersByKey[$key];
    }

    /**
     * Instantiate item (formatter, processor, handler) from configuration
     *
     * @param array|string $item
     * @param string $type
     * @return array|mixed
     */
    private function instantiateItem($item, string $type)
    {
        $item = $this->unifyItemConfigurationFormat($item);

        $arguments = $this->getArguments($item, $type);
        $object = $this->objectInstantiator->createInstance($item['class'], $arguments);

        if ($type === self::TYPE_HANDLER && isset($item['formatter'])) {
            $object->setFormatter($this->getOrInstantiateFormatterByKey($item['formatter']));
        }
        if ($type === self::TYPE_HANDLER && isset($item['processors']) && is_array($item['processors'])) {
            foreach ($item['processors'] as $processorKey) {
                $object->pushProcessor($this->getOrInstantiateProcessorByKey($processorKey));
            }
        }

        return $object;
    }

    /**
     * Configuration of items can be done using string and array.
     * Unify them to a constant array format
     *
     * @param array|string $item
     * @return array
     */
    private function unifyItemConfigurationFormat($item): array
    {
        if (is_string($item)) {
            $item = [ 'class' => $item ];
        }

        if (!is_array($item) || !isset($item['class'])) {
            throw new \InvalidArgumentException('Cannot instantiate object for item of type: ' . gettype($item));
        }

        return $item;
    }

    /**
     * Get arguments for constructor
     *
     * @param array $item
     * @param string $type
     * @return array
     */
    private function getArguments(array $item, string $type): array
    {
        $arguments = $item;
        if ($type === self::TYPE_HANDLER) {
            $arguments = $this->processHandlerArguments($arguments);
        }

        foreach (array_keys($arguments) as $argumentKey) {
            if (substr($argumentKey, 0, 1) !== '@') {
                continue;
            }
            $arguments[substr($argumentKey, 1)] = $this->instantiateItem(
                $arguments[$argumentKey],
                self::TYPE_OTHER
            );
            unset($arguments[$argumentKey]);
        }

        unset($arguments['class']);
        return $arguments;
    }

    /**
     * Handlers allow some specific arguments to be passed. Process them
     *
     * @param array $arguments
     * @return array
     */
    private function processHandlerArguments(array $arguments): array
    {
        if (array_key_exists('handler', $arguments)) {
            $arguments['handler'] = $this->getOrInstantiateHandlerByKey($arguments['handler']);
        }
        if (array_key_exists('handlers', $arguments) && is_array($arguments['handlers'])) {
            $handlers = [];
            foreach ($arguments['handlers'] as $handlerKey) {
                $handlers[] = $this->getOrInstantiateHandlerByKey($handlerKey);
            }
            $arguments['handlers'] = $handlers;
        }

        unset($arguments['formatter']);
        unset($arguments['processors']);

        return $arguments;
    }
}
