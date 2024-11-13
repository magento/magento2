<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config;

use Magento\Framework\Mview\View\AdditionalColumnsProcessor\DefaultProcessor;
use Magento\Framework\Mview\View\ChangelogBatchWalker;
use Magento\Framework\Mview\View\SubscriptionInterface;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var string
     */
    private $defaultProcessor;

    /**
     * @var string
     */
    private $defaultIterator;

    /**
     * @param string $defaultProcessor
     * @param string $defaultIterator
     */
    public function __construct(
        string $defaultProcessor = DefaultProcessor::class,
        string $defaultIterator = ChangelogBatchWalker::class
    ) {
        $this->defaultProcessor = $defaultProcessor;
        $this->defaultIterator = $defaultIterator;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \DOMXPath($source);
        $views = $xpath->evaluate('/config/view');
        /** @var $viewNode \DOMNode */
        foreach ($views as $viewNode) {
            $data = [];
            $viewId = $this->getAttributeValue($viewNode, 'id');
            $data['view_id'] = $viewId;
            $data['action_class'] = $this->getAttributeValue($viewNode, 'class');
            $data['group'] = $this->getAttributeValue($viewNode, 'group');
            $data['walker'] = $this->getAttributeValue($viewNode, 'walker') ?: $this->defaultIterator;
            $data['subscriptions'] = [];

            /** @var $childNode \DOMNode */
            foreach ($viewNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $data = $this->convertChild($childNode, $data);
            }
            $output[$viewId] = $data;
        }
        return $output;
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param mixed $default
     * @return null|string
     */
    protected function getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }

    /**
     * Convert child from dom to array
     *
     * @param \DOMNode $childNode
     * @param array $data
     * @return array
     */
    protected function convertChild(\DOMNode $childNode, $data)
    {
        switch ($childNode->nodeName) {
            case 'subscriptions':
                /** @var $subscription \DOMNode */
                foreach ($childNode->childNodes as $subscription) {
                    if ($subscription->nodeType != XML_ELEMENT_NODE || $subscription->nodeName != 'table') {
                        continue;
                    }
                    $name = $this->getAttributeValue($subscription, 'name');
                    $column = $this->getAttributeValue($subscription, 'entity_column');
                    $subscriptionModel = $this->getAttributeValue($subscription, 'subscription_model');

                    if (!empty($subscriptionModel)
                        && !in_array(
                            SubscriptionInterface::class,
                            class_implements(ltrim($subscriptionModel, '\\'))
                        )
                    ) {
                        throw new \InvalidArgumentException(
                            'Subscription model must implement ' . SubscriptionInterface::class
                        );
                    }
                    $data['subscriptions'][$name] = [
                        'name' => $name,
                        'column' => $column,
                        'subscription_model' => $subscriptionModel,
                        'additional_columns' => $this->getAdditionalColumns($subscription),
                        'processor' => $this->getAttributeValue($subscription, 'processor')
                            ?: $this->defaultProcessor
                    ];
                }
                break;
        }
        return $data;
    }

    /**
     * Retrieve additional columns of subscription table
     *
     * @param \DOMNode $subscription
     * @return array
     */
    private function getAdditionalColumns(\DOMNode $subscription): array
    {
        $additionalColumns = [];
        foreach ($subscription->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE || $childNode->nodeName != 'additionalColumns') {
                continue;
            }

            foreach ($childNode->childNodes as $columnNode) {
                if ($columnNode->nodeName !== 'column') {
                    continue;
                }

                $additionalColumns[$this->getAttributeValue($columnNode, 'name')] = [
                    'name' => $this->getAttributeValue($columnNode, 'name'),
                    'cl_name' => $this->getAttributeValue($columnNode, 'cl_name'),
                    'constant' => $this->getAttributeValue($columnNode, 'constant'),
                ];
            }
        }

        return $additionalColumns;
    }
}
