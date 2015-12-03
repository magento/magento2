<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Validator;

use Magento\Framework\Communication\ConfigInterface;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Reflection\TypeProcessor;

/**
 * Communication configuration validator. Validates data, that have been read from env.php.
 */
class EnvValidator
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @param MethodsMap $methodsMap
     * @param BooleanUtils $booleanUtils
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        MethodsMap $methodsMap,
        BooleanUtils $booleanUtils,
        TypeProcessor $typeProcessor
    ) {
        $this->methodsMap = $methodsMap;
        $this->booleanUtils = $booleanUtils;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * Validate config data
     *
     * @param array $configData
     */
    public function validate($configData)
    {
        foreach ($configData as $topicName => $configDataItem) {
            $this->validateTopicName($configDataItem, $topicName);
            $this->validateTopic($configDataItem,  $topicName);
            $this->validateTopicResponseHandler($configDataItem);
            $this->validateRequestSchema($configDataItem);
            $this->validateResponseSchema($configDataItem);
        }
    }

    /**
     * Validate topic name from config data
     *
     * @param mixed $configDataItem
     * @param string $topicName
     * @return void
     */
    private function validateTopicName($configDataItem, $topicName)
    {
        if (!is_string($topicName)) {
            throw new \LogicException(sprintf('Topic "%s" must contain a name', $topicName));
        }
        if (isset($configDataItem[ConfigInterface::TOPIC_NAME])) {
            if ($configDataItem[ConfigInterface::TOPIC_NAME] != $topicName) {
                throw new \LogicException(
                    sprintf(
                        'Topic name "%s" and attribute "name" = "%s" must be equal',
                        $topicName,
                        $configDataItem[ConfigInterface::TOPIC_NAME]
                    )
                );
            }
        }
    }

    /**
     * Validate topic from config data
     *
     * @param mixed $configDataItem
     * @param string $topicName
     * @return void
     */
    private function validateTopic($configDataItem, $topicName)
    {
        $requiredFields = [
            ConfigInterface::TOPIC_NAME,
            ConfigInterface::TOPIC_IS_SYNCHRONOUS,
            ConfigInterface::TOPIC_REQUEST,
            ConfigInterface::TOPIC_REQUEST_TYPE,
            ConfigInterface::TOPIC_RESPONSE,
            ConfigInterface::TOPIC_HANDLERS
        ];

        if (!is_array($configDataItem)) {
            throw new \LogicException(
                sprintf('Topic "%s" must contain data', $topicName)
            );
        }
        $configDataItemKeys = array_keys($configDataItem);
        $missedKeys = array_diff($requiredFields, $configDataItemKeys);
        if (!empty($missedKeys)) {
            throw new \LogicException(
                sprintf(
                    'Topic "%s" has missed keys: [%s]',
                    $configDataItem[ConfigInterface::TOPIC_NAME],
                    implode(', ', $missedKeys)
                )
            );
        }
        $excessiveKeys = array_diff($configDataItemKeys, $requiredFields);
        if (!empty($excessiveKeys)) {
            throw new \LogicException(
                sprintf(
                    'Topic "%s" has excessive keys: [%s]',
                    $configDataItem[ConfigInterface::TOPIC_NAME],
                    implode(', ', $excessiveKeys)
                )
            );
        }
    }

    /**
     * Validate topic response handler from config data
     *
     * @param array $configDataItem
     * @return void
     */
    private function validateTopicResponseHandler($configDataItem)
    {
        if (!is_array($configDataItem[ConfigInterface::TOPIC_HANDLERS])) {
            throw new \LogicException(
                sprintf(
                    'Handlers in the topic "%s" must be an array',
                    $configDataItem[ConfigInterface::TOPIC_NAME]
                )
            );
        }
        if (
            $this->booleanUtils->toBoolean($configDataItem[ConfigInterface::TOPIC_IS_SYNCHRONOUS]) &&
            count($configDataItem[ConfigInterface::TOPIC_HANDLERS]) != 1
        ) {
            throw new \LogicException(
                sprintf(
                    'Topic "%s" is configured for synchronous requests, that is why it must have exactly one '
                    . 'response handler declared. The following handlers declared: %s',
                    $configDataItem[ConfigInterface::TOPIC_NAME],
                    implode(', ', array_keys($configDataItem[ConfigInterface::TOPIC_HANDLERS]))
                )
            );
        }

        foreach ($configDataItem[ConfigInterface::TOPIC_HANDLERS] as $handlerName => $handler) {
            $serviceName = $handler[ConfigInterface::HANDLER_TYPE];
            $methodName = $handler[ConfigInterface::HANDLER_METHOD];
            if (isset($handler[ConfigInterface::HANDLER_DISABLED]) &&
                $this->booleanUtils->toBoolean($handler[ConfigInterface::HANDLER_DISABLED])
            ) {
                throw new \LogicException(
                    sprintf(
                        'Disabled handler "%s" for topic "%s" cannot be added to the config file',
                        $handlerName,
                        $configDataItem[ConfigInterface::TOPIC_NAME]
                    )
                );
            }
            try {
                $this->methodsMap->getMethodParams($serviceName, $methodName);
            } catch (\Exception $e) {
                throw new \LogicException(
                    sprintf(
                        'Service method specified in the definition of handler "%s" for topic "%s"'
                        . ' is not available. Given "%s"',
                        $handlerName,
                        $configDataItem[ConfigInterface::TOPIC_NAME],
                        $serviceName . '::' . $methodName
                    )
                );
            }
        }
    }

    /**
     * Validate request schema from config data
     *
     * @param array $configDataItem
     * @return void
     */
    private function validateRequestSchema($configDataItem)
    {
        $topicName = $configDataItem[ConfigInterface::TOPIC_NAME];
        $requestSchema = $configDataItem[ConfigInterface::TOPIC_REQUEST];

        if (!in_array(
            $configDataItem[ConfigInterface::TOPIC_REQUEST_TYPE],
            [ConfigInterface::TOPIC_REQUEST_TYPE_CLASS, ConfigInterface::TOPIC_REQUEST_TYPE_METHOD]
        )
        ) {
            throw new \LogicException(
                sprintf(
                    'Request schema type for topic "%s" must be "%s" or "%s". Given "%s"',
                    $topicName,
                    ConfigInterface::TOPIC_REQUEST_TYPE_CLASS,
                    ConfigInterface::TOPIC_REQUEST_TYPE_METHOD,
                    $requestSchema
                )
            );
        }
        {
            if ($configDataItem[ConfigInterface::TOPIC_REQUEST_TYPE] == ConfigInterface::TOPIC_REQUEST_TYPE_CLASS) {
                try {
                    $this->methodsMap->getMethodsMap($requestSchema);
                } catch (\Exception $e) {
                    throw new \LogicException(
                        sprintf(
                            'Request schema definition for topic "%s" should reference existing service class. '
                            . 'Given "%s"',
                            $topicName,
                            $requestSchema
                        )
                    );
                }
            }
        }
    }

    /**
     * Validate response schema from config data
     *
     * @param array $configDataItem
     * @return void
     */
    private function validateResponseSchema($configDataItem)
    {
        $topicName = $configDataItem[ConfigInterface::TOPIC_NAME];
        $responseSchema = $configDataItem[ConfigInterface::TOPIC_RESPONSE];
        if ($responseSchema) {
            try {
                $this->typeProcessor->register($responseSchema);
            } catch (\Exception $e) {
                throw new \LogicException(
                    sprintf(
                        'Response schema definition for topic "%s" should reference existing type or service class. '
                        . 'Given "%s"',
                        $topicName,
                        $responseSchema
                    )
                );
            }
        }
    }
}
