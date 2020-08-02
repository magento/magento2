<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Xml;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\Communication\Config\ConfigParser;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Converts MessageQueue consumers config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var string
     */
    private static $defaultInstance = ConsumerInterface::class;

    /**
     * @var ConfigParser
     */
    private $configParser;

    /**
     * Default value provider.
     *
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param ConfigParser $configParser
     * @param DefaultValueProvider $defaultValueProvider
     */
    public function __construct(ConfigParser $configParser, DefaultValueProvider $defaultValueProvider)
    {
        $this->configParser = $configParser;
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $result = [];
        /** @var $consumerNode \DOMElement */
        foreach ($source->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $this->getAttributeValue($consumerNode, 'name');
            $handler = $this->getAttributeValue($consumerNode, 'handler');
            $onlySpawnWhenMessageAvailable =  $this->getAttributeValue(
                $consumerNode,
                'onlySpawnWhenMessageAvailable'
            );

            $result[$consumerName] = [
                'name' => $consumerName,
                'queue' => $this->getAttributeValue($consumerNode, 'queue'),
                'consumerInstance' => $this->getAttributeValue(
                    $consumerNode,
                    'consumerInstance',
                    self::$defaultInstance
                ),
                'handlers' => $handler ? [$this->parseHandler($handler)] : [],
                'connection' => $this->getAttributeValue(
                    $consumerNode,
                    'connection',
                    $this->defaultValueProvider->getConnection()
                ),
                'maxMessages' => $this->getAttributeValue($consumerNode, 'maxMessages'),
                'maxIdleTime' => $this->getAttributeValue($consumerNode, 'maxIdleTime'),
                'sleep' => $this->getAttributeValue($consumerNode, 'sleep'),
                'onlySpawnWhenMessageAvailable' =>
                    $onlySpawnWhenMessageAvailable === null ? null : boolval($onlySpawnWhenMessageAvailable)
            ];
        }
        return $result;
    }

    /**
     * Get attribute value of the given node
     *
     * @param \DOMNode $node
     * @param string $attributeName
     * @param mixed $default
     * @return string|null
     */
    private function getAttributeValue(\DOMNode $node, $attributeName, $default = null)
    {
        $item = $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }

    /**
     * Parse service method callback to become compatible with handlers format.
     *
     * @param array $handler
     * @return array
     */
    private function parseHandler($handler)
    {
        $parseServiceMethod = $this->configParser->parseServiceMethod($handler);
        return [
            CommunicationConfig::HANDLER_TYPE => $parseServiceMethod[ConfigParser::TYPE_NAME],
            CommunicationConfig::HANDLER_METHOD => $parseServiceMethod[ConfigParser::METHOD_NAME]
        ];
    }
}
