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
 * @since 2.2.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private static $defaultInstance = ConsumerInterface::class;

    /**
     * @var ConfigParser
     * @since 2.2.0
     */
    private $configParser;

    /**
     * Default value provider.
     *
     * @var DefaultValueProvider
     * @since 2.2.0
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param ConfigParser $configParser
     * @param DefaultValueProvider $defaultValueProvider
     * @since 2.2.0
     */
    public function __construct(ConfigParser $configParser, DefaultValueProvider $defaultValueProvider)
    {
        $this->configParser = $configParser;
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * {@inheritDoc}
     * @since 2.2.0
     */
    public function convert($source)
    {
        $result = [];
        /** @var $consumerNode \DOMElement */
        foreach ($source->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $this->getAttributeValue($consumerNode, 'name');
            $handler = $this->getAttributeValue($consumerNode, 'handler');
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
                'maxMessages' => $this->getAttributeValue($consumerNode, 'maxMessages')
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
     * @since 2.2.0
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
     * @since 2.2.0
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
