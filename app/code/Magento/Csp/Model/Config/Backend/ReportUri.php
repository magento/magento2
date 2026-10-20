<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Config\Backend;

use Magento\Csp\Model\Mode\ReportUriValidator;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class ReportUri extends Value
{
    /**
     * @var ReportUriValidator
     */
    private ReportUriValidator $reportUriValidator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ReportUriValidator $reportUriValidator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ReportUriValidator $reportUriValidator,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reportUriValidator = $reportUriValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue();

        if ($value !== '' && !$this->reportUriValidator->isValid($value)) {
            $field = $this->getFieldConfig();
            $label = $field && is_array($field) ? $field['label'] : 'Report URI';
            throw new LocalizedException(
                __('Invalid %1. Specify an absolute http(s) URL or a path starting with "/".', $label)
            );
        }

        return parent::beforeSave();
    }
}
