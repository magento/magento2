<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Url as UrlValidator;

class ReportUri extends Value
{
    /**
     * @var UrlValidator
     */
    private UrlValidator $urlValidator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param UrlValidator $urlValidator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UrlValidator $urlValidator,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlValidator = $urlValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue();

        if ($value !== '' && !$this->urlValidator->isValid($value, ['http', 'https'])) {
            $field = $this->getFieldConfig();
            $label = $field && is_array($field) ? $field['label'] : 'Report URI';
            throw new LocalizedException(__('Invalid %1. Specify a fully qualified URL.', $label));
        }

        return parent::beforeSave();
    }
}
