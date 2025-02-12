<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Handles backpressure "period" config value
 */
class PeriodValue extends Value
{
    /**
     * @var PeriodSource
     */
    private PeriodSource $source;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param PeriodSource $source
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        PeriodSource $source,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
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
            $value = (string)$this->getValue();
            $availableValues = $this->source->toOptionArray();
            if (!array_key_exists($value, $availableValues)) {
                throw new LocalizedException(
                    __(
                        'Please select a valid rate limit period in seconds: %1',
                        implode(', ', array_keys($availableValues))
                    )
                );
            }
        }

        return $this;
    }
}
