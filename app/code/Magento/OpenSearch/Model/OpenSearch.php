<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model;

/**
 * The purpose of this class is adding the support for opensearch version 2
 */

use Magento\Framework\App\ObjectManager;
use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;

class OpenSearch extends SearchClient
{
    /**
     * @var DynamicTemplatesProvider|null
     */
    private $dynamicTemplatesProvider;

    /**
     * @param $options
     * @param $openSearchClient
     * @param $fieldsMappingPreprocessors
     * @param DynamicTemplatesProvider|null $dynamicTemplatesProvider
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct($options = [], $openSearchClient = null, $fieldsMappingPreprocessors = [], ?DynamicTemplatesProvider $dynamicTemplatesProvider = null)
    {
        $this->dynamicTemplatesProvider = $dynamicTemplatesProvider ?: ObjectManager::getInstance()
            ->get(DynamicTemplatesProvider::class);
        parent::__construct($options, $openSearchClient, $fieldsMappingPreprocessors, $dynamicTemplatesProvider);
    }

    /**
     * Add mapping to OpenSearch index
     *
     * @param array $fields
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function addFieldsMapping(array $fields, string $index, string $entityType)
    {
        $params = [
            'index' => $index,
            'body' => [
                'properties' => [],
                'dynamic_templates' => $this->dynamicTemplatesProvider->getTemplates(),
            ],
        ];

        foreach ($this->applyFieldsMappingPreprocessors($fields) as $field => $fieldInfo) {
            $params['body']['properties'][$field] = $fieldInfo;
        }

        $this->getOpenSearchClient()->indices()->putMapping($params);
    }

    /**
     * Execute search by $query
     *
     * @param array $query
     * @return array
     */
    public function query(array $query): array
    {
        unset($query['type']);
        return $this->getOpenSearchClient()->search($query);
    }


    /**
     * Delete mapping in OpenSearch index
     *
     * @param string $index
     * @return void
     */
    public function deleteMapping(string $index, string $entityType)
    {
        $this->getOpenSearchClient()->indices()->deleteMapping(
            [
                'index' => $index
            ]
        );
    }
}
