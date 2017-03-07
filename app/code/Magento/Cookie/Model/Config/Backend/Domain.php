<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Model\Config\Backend;

/**
 * Backend model for domain config value
 */
class Domain extends \Magento\Framework\App\Config\Value
{
    /** @var \Magento\Framework\Session\Config\Validator\CookieDomainValidator */
    protected $configValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Session\Config\Validator\CookieDomainValidator $configValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Session\Config\Validator\CookieDomainValidator $configValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValidator = $configValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a domain name value
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        // Empty value is treated valid and will be handled when read the value out
        if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Invalid domain name: %1', join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
    }
}
