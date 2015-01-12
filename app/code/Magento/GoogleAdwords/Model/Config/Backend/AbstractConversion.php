<?php
/**
 * Google AdWords Conversion Abstract Backend model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractConversion extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Validator\Object
     */
    protected $_validatorComposite;

    /**
     * @var \Magento\GoogleAdwords\Model\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Validator\ObjectFactory $validatorCompositeFactory
     * @param \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Validator\ObjectFactory $validatorCompositeFactory,
        \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);

        $this->_validatorFactory = $validatorFactory;
        $this->_validatorComposite = $validatorCompositeFactory->create();
    }
}
