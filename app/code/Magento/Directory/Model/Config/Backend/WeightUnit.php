<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Config\Backend;

use Magento\Directory\Model\Config\Source\WeightUnit as Source;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend source for weight unit configuration field
 */
class WeightUnit extends Value
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @param Source $source
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        Source $source,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->source = $source;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether weight unit value is acceptable or not
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            $weightUnit = $this->getData('value');
            if (!in_array($weightUnit, $this->getOptions())) {
                throw new LocalizedException(__('There was an error save new configuration value.'));
            }
        }

        return parent::beforeSave();
    }

    /**
     * Get available options for weight unit
     *
     * @return array
     */
    private function getOptions()
    {
        $options = $this->source->toOptionArray();

        return array_column($options, 'value');
    }
}
