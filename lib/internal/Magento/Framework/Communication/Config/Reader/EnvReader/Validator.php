<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader\EnvReader;

use Magento\Framework\Communication\Config\Validator as ConfigValidator;
use Magento\Framework\Communication\ConfigInterface;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Communication configuration validator. Validates data, that have been read from env.php.
 * @since 2.1.0
 */
class Validator extends ConfigValidator
{
    /**
     * @var TypeProcessor
     * @since 2.1.0
     */
    private $typeProcessor;

    /**
     * @var MethodsMap
     * @since 2.1.0
     */
    private $methodsMap;

    /**
     * @var BooleanUtils
     * @since 2.1.0
     */
    private $booleanUtils;

    /**
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     * @param BooleanUtils $booleanUtils
     * @since 2.1.0
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMap,
        BooleanUtils $booleanUtils
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->typeProcessor = $typeProcessor;
        $this->methodsMap = $methodsMap;
        parent::__construct($typeProcessor, $methodsMap);
    }

    /**
     * Validate config data
     *
     * @param array $configData
     * @return void
     * @since 2.1.0
     */
    public function validate($configData)
    {
        if (isset($configData[ConfigInterface::TOPICS])) {
            foreach ($configData[ConfigInterface::TOPICS] as $topicNameKey => $configDataItem) {
                $this->validateTopicName($configDataItem, $topicNameKey);
                $this->validateTopic($configDataItem, $topicNameKey);

                $topicName = $configDataItem[ConfigInterface::TOPIC_NAME];
                $responseSchema = $configDataItem[ConfigInterface::TOPIC_RESPONSE];
                $requestSchema = $configDataItem[ConfigInterface::TOPIC_REQUEST];
                $requestType = $configDataItem[ConfigInterface::TOPIC_REQUEST_TYPE];

                $this->validateTopicResponseHandler($configDataItem);
                $this->validateRequestTypeValue($requestType, $topicName, $requestSchema);
                if ($requestType == ConfigInterface::TOPIC_REQUEST_TYPE_CLASS) {
                    $this->validateRequestSchemaType($requestSchema, $topicName);
                }
                if ($responseSchema) {
                    $this->validateResponseSchemaType($responseSchema, $topicName);
                }
            }
        }
    }

    /**
     * Validate topic name from config data
     *
     * @param mixed $configDataItem
     * @param string $topicName
     * @return void
     * @since 2.1.0
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
     * @since 2.1.0
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
        try {
            $this->booleanUtils->toBoolean($configDataItem[ConfigInterface::TOPIC_IS_SYNCHRONOUS]);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'The attribute "%s" for topic "%s" should have the value of the boolean type. '
                    . 'Given value is "%s"',
                    ConfigInterface::TOPIC_IS_SYNCHRONOUS,
                    $configDataItem[ConfigInterface::TOPIC_NAME],
                    var_export($configDataItem[ConfigInterface::TOPIC_IS_SYNCHRONOUS], true)
                )
            );
        }
    }

    /**
     * Validate topic response handler from config data
     *
     * @param array $configDataItem
     * @return void
     * @since 2.1.0
     */
    private function validateTopicResponseHandler($configDataItem)
    {
        $topicName = $configDataItem[ConfigInterface::TOPIC_NAME];
        if (!is_array($configDataItem[ConfigInterface::TOPIC_HANDLERS])) {
            throw new \LogicException(
                sprintf(
                    'Handlers in the topic "%s" must be an array',
                    $topicName
                )
            );
        }
        if ($this->booleanUtils->toBoolean($configDataItem[ConfigInterface::TOPIC_IS_SYNCHRONOUS]) &&
            count($configDataItem[ConfigInterface::TOPIC_HANDLERS]) != 1
        ) {
            throw new \LogicException(
                sprintf(
                    'Topic "%s" is configured for synchronous requests, that is why it must have exactly one '
                    . 'response handler declared. The following handlers declared: %s',
                    $topicName,
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
                        $topicName
                    )
                );
            }
            $this->validateResponseHandlersType($serviceName, $methodName, $handlerName, $topicName);
        }
    }

    /**
     * @param string $requestType
     * @param string $topicName
     * @param string $requestSchema
     * @return void
     * @since 2.1.0
     */
    protected function validateRequestTypeValue($requestType, $topicName, $requestSchema)
    {
        if (!in_array(
            $requestType,
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
    }
}
