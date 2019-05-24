<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Catalog search range request generator.
 */
class Decimal implements GeneratorInterface
{
    /**
     * @var \Magento\Store\Model\ScopeInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Store\Model\ScopeInterface|null $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig = null)
    {
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getFilterData(Attribute $attribute, $filterName)
    {
        return [
            'type' => FilterInterface::TYPE_RANGE,
            'name' => $filterName,
            'field' => $attribute->getAttributeCode(),
            'from' => '$' . $attribute->getAttributeCode() . '.from$',
            'to' => '$' . $attribute->getAttributeCode() . '.to$',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAggregationData(Attribute $attribute, $bucketName)
    {
        return [
            'type' => BucketInterface::TYPE_DYNAMIC,
            'name' => $bucketName,
            'field' => $attribute->getAttributeCode(),
            'method' => $this->getRangeCalculation(),
            'metric' => [['type' => 'count']],
        ];
    }

    /**
     * Get range calculation by what was set in the configuration
     *
     * @return string
     */
    private function getRangeCalculation(): string
    {
        return $this->scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
    }
}
