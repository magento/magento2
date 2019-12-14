<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\DataProvider\Order;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Document
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{

    /**
     * @var string
     */
    private static $customerGroupAttributeCode = 'customer_group';

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Document constructor.
     * @param AttributeValueFactory $attributeValueFactory
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($attributeValueFactory);
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttribute($attributeCode)
    {
        if (self::$customerGroupAttributeCode === $attributeCode) {
            $this->setCustomerGroupValue();
        }
        return parent::getCustomAttribute($attributeCode);
    }
    /**
     * Update customer group value
     * Method set group code instead id value
     * @return void
     */
    private function setCustomerGroupValue()
    {
        $value = $this->getData(self::$customerGroupAttributeCode);
        try {
            $group = $this->groupRepository->getById($value);
            $this->setCustomAttribute(self::$customerGroupAttributeCode, $group->getCode());
        } catch (NoSuchEntityException $e) {
            $this->setCustomAttribute(self::$customerGroupAttributeCode, 'N/A');
        }
    }
}
