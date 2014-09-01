<?php
/**
 * Search Request Pool
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search;

class RequestFactory
{
    const CACHE_PREFIX = 'search_request::';

    /**
     * @var Request\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * Request Pool constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Search\Request\Config $config
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\Search\Request\Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Create Request instance with specified parameters
     *
     * @param string $requestName
     * @param array $bindValues
     * @return \Magento\Framework\Search\Request
     * @throws \InvalidArgumentException
     */
    public function create($requestName, array $bindValues = array())
    {
        $data = $this->config->get($requestName);
        if (is_null($data)) {
            throw new \InvalidArgumentException("Request name '{$requestName}' doesn't exist.");
        }
        $data = $this->replaceBinds((array)$data, array_keys($bindValues), array_values($bindValues));
        return $this->convert($data);
    }

    /**
     * @param string|array $data
     * @param string[] $bindKeys
     * @param string[] $bindValues
     * @return string|array
     */
    private function replaceBinds($data, $bindKeys, $bindValues)
    {
        if (is_scalar($data)) {
            return str_replace($bindKeys, $bindValues, $data);
        } else {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceBinds($value, $bindKeys, $bindValues);
            }
            return $data;
        }
    }

    /**
     * Convert array to Request instance
     *
     * @param array $data
     * @return \Magento\Framework\Search\Request
     */
    private function convert($data)
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->objectManager->create(
            'Magento\Framework\Search\Request\Mapper',
            [
                'objectManager' => $this->objectManager,
                'rootQueryName' => $data['query'],
                'queries' => $data['queries'],
                'aggregation' => $data['aggregation'],
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
                'demensions' => array_map(
                    function ($data) {
                        return $this->objectManager->create(
                            'Magento\Framework\Search\Request\Dimension',
                            $data
                        );
                    },
                    isset($data['demensions']) ? $data['demensions'] : []
                ),
                'buckets' => $mapper->getBuckets()
            ]
        );
    }
}
