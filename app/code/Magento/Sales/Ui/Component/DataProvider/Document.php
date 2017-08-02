<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Invoice;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Document
 * @since 2.1.0
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     * @since 2.1.0
     */
    private static $stateAttributeCode = 'state';

    /**
     * @var string
     * @since 2.1.0
     */
    private static $customerGroupAttributeCode = 'customer_group_id';

    /**
     * @var GroupRepositoryInterface
     * @since 2.1.0
     */
    private $groupRepository;

    /**
     * Document constructor.
     * @param AttributeValueFactory $attributeValueFactory
     * @param GroupRepositoryInterface $groupRepository
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
