<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Backend\Cookie;

use Magento\Framework\App\Config\Value;

/**
 * Backend model for domain config value
 */
class Lifetime extends \Magento\Framework\App\Config\Value
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator */
    protected $configValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->configValidator = $configValidator;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a domain name value
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Invalid cookie lifetime: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Model\Exception($msg);
        }
    }
}
