<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

/**
 * Admin User Expiration model.
 * @method string getExpiresAt()
 * @method \Magento\Security\Model\UserExpiration setExpiresAt(string $value)
 */
class UserExpiration extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var UserExpiration\Validator
     */
    private $validator;

    /**
     * UserExpiration constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param UserExpiration\Validator $validator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Security\Model\UserExpiration\Validator $validator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->validator = $validator;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Security\Model\ResourceModel\UserExpiration::class);
    }

    /**
     * TODO: remove and use a plugin on UserValidationRules
     */
//    protected function _getValidationRulesBeforeSave()
//    {
//        return $this->validator;
//    }
}
