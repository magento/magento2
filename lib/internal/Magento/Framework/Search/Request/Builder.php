<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\RequestInterface;

class Builder
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Binder
     */
    private $binder;

    /**
     * @var array
     */
    private $data = [
        'dimensions' => [],
        'placeholder' => [],
    ];
    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * Request Builder constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param Binder $binder
     * @param Cleaner $cleaner
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config, Binder $binder, Cleaner $cleaner)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->binder = $binder;
        $this->cleaner = $cleaner;
    }

    /**
     * Set request name
     *
     * @param string $requestName
     * @return $this
     */
    public function setRequestName($requestName)
    {
        $this->data['requestName'] = $requestName;
        return $this;
    }

    /**
     * Set size
     *
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->data['size'] = $size;
        return $this;
    }

    /**
     * Set from
     *
     * @param int $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->data['from'] = $from;
        return $this;
    }

    /**
     * Bind dimension data by name
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function bindDimension($name, $value)
    {
        $this->data['dimensions'][$name] = $value;
        return $this;
    }

    /**
     * Bind data to placeholder
     *
     * @param string $placeholder
     * @param mixed $value
     * @return $this
     */
    public function bind($placeholder, $value)
    {
        $this->data['placeholder']['$' . $placeholder . '$'] = $value;
        return $this;
    }

    /**
     * Create request object
     *
     * @return RequestInterface
     */
    public function create()
    {
        if (!isset($this->data['requestName'])) {
            throw new \InvalidArgumentException("Request name not defined.");
        }
        $requestName = $this->data['requestName'];
        /** @var array $data */
        $data = $this->config->get($requestName);
        if ($data === null) {
            throw new \InvalidArgumentException("Request name '{$requestName}' doesn't exist.");
        }

        $data = $this->binder->bind($data, $this->data);
        $data = $this->cleaner->clean($data);

        $this->clear();

        return $this->convert($data);
    }

    /**
     * Clear data
     *
     * @return void
     */
    private function clear()
    {
        $this->data = [
            'dimensions' => [],
            'placeholder' => [],
        ];
    }

    /**
     * Convert array to Request instance
     *
     * @param array $data
     * @return RequestInterface
     */
    private function convert($data)
    {
        /** @var Mapper $mapper */
        $mapper = $this->objectManager->create(
            'Magento\Framework\Search\Request\Mapper',
            [
                'objectManager' => $this->objectManager,
                'rootQueryName' => $data['query'],
                'queries' => $data['queries'],
                'aggregations' => $data['aggregations'],
                'filters' => $data['filters']
            ]
        );
        return $this->objectManager->create(
            'Magento\Framework\Search\Request',
            [
                'name' => $data['query'],
                'indexName' => $data['index'],
                'from' => $data['from'],
                'size' => $data['size'],
                'query' => $mapper->getRootQuery(),
                'dimensions' => $this->buildDimensions(isset($data['dimensions']) ? $data['dimensions'] : []),
                'buckets' => $mapper->getBuckets()
            ]
        );
    }

    /**
     * @param array $dimensionsData
     * @return array
     */
    private function buildDimensions(array $dimensionsData)
    {
        $dimensions = [];
        foreach ($dimensionsData as $dimensionData) {
            $dimensions[$dimensionData['name']] = $this->objectManager->create(
                'Magento\Framework\Search\Request\Dimension',
                $dimensionData
            );
        }
        return $dimensions;
    }
}
