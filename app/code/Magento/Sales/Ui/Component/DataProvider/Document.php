<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Invoice;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Document
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    private static $stateAttributeCode = 'state';

    /**
     * @var string
     */
    private static $customerGroupAttributeCode = 'customer_group_id';

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
        switch ($attributeCode) {
            case self::$stateAttributeCode:
                $this->setStateValue();
                break;
            case self::$customerGroupAttributeCode:
                $this->setCustomerGroupValue();
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }

    /**
     * Update invoice state value
     * Method set text label instead id value
     * @return void
     */
    private function setStateValue()
    {
        $value = $this->getData(self::$stateAttributeCode);
        /** @var \Magento\Framework\Phrase $state */
        $state = Invoice::getStates()[$value];

        $this->setCustomAttribute(self::$stateAttributeCode, $state->getText());
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
