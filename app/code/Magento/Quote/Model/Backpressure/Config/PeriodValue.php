<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Handles backpressure "period" config value.
 */
class PeriodValue extends Value
{
    /**
     * @var PeriodSource
     */
    private PeriodSource $source;

    /**
     * PeriodValue constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param PeriodSource $source
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        PeriodSource $source,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->source = $source;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            $value = (string) $this->getValue();
            if (!array_key_exists($value, $this->source->toOptionArray())) {
                throw new LocalizedException(__('Please select a valid rate limit period'));
            }
        }

        return $this;
    }
}
