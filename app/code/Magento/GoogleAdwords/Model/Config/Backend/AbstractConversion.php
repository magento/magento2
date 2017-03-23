<?php
/**
 * Google AdWords Conversion Abstract Backend model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractConversion extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Validator\DataObject
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
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Validator\DataObjectFactory $validatorCompositeFactory
     * @param \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Validator\DataObjectFactory $validatorCompositeFactory,
        \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->_validatorFactory = $validatorFactory;
        $this->_validatorComposite = $validatorCompositeFactory->create();
    }
}
