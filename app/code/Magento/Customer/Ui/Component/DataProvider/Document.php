<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\DataProvider;

use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Document
 *
 * Set the attribute label and value for UI Component
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    private static $genderAttributeCode = 'gender';

    /**
     * @var string
     */
    private static $groupAttributeCode = 'group_id';

    /**
     * @var string
     */
    private static $websiteAttributeCode = 'website_id';

    /**
     * @var string
     */
    private static $websiteIdAttributeCode = 'original_website_id';

    /**
     * @var string
     */
    private static $confirmationAttributeCode = 'confirmation';

    /**
     * @var string
     */
    private static $accountLockAttributeCode = 'lock_expires';

    /**
     * @var array
     */
    private static $customerGroupCodeById = [];

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Document constructor.
     *
     * @param AttributeValueFactory $attributeValueFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param CustomerMetadataInterface $customerMetadata
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        GroupRepositoryInterface $groupRepository,
        CustomerMetadataInterface $customerMetadata,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig = null
    ) {
        parent::__construct($attributeValueFactory);
        $this->customerMetadata = $customerMetadata;
        $this->groupRepository = $groupRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->create(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttribute($attributeCode)
    {
        switch ($attributeCode) {
            case self::$genderAttributeCode:
                $this->setGenderValue();
                break;
            case self::$groupAttributeCode:
                $this->setCustomerGroupValue();
                break;
            case self::$websiteAttributeCode:
                $this->setWebsiteValue();
                break;
            case self::$confirmationAttributeCode:
                $this->setConfirmationValue();
                break;
            case self::$accountLockAttributeCode:
                $this->setAccountLockValue();
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }

    /**
     * Update customer gender value. Method set gender label instead of id value
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setGenderValue()
    {
        $value = $this->getData(self::$genderAttributeCode);

        if (!$value) {
            $this->setCustomAttribute(self::$genderAttributeCode, 'N/A');
            return;
        }

        try {
            $attributeMetadata = $this->customerMetadata->getAttributeMetadata(self::$genderAttributeCode);
            $options = $attributeMetadata->getOptions();
            array_walk(
                $options,
                function (OptionInterface $option) use ($value) {
                    if ($option->getValue() == $value) {
                        $this->setCustomAttribute(self::$genderAttributeCode, $option->getLabel());
                    }
                }
            );
        } catch (NoSuchEntityException $e) {
            $this->setCustomAttribute(self::$genderAttributeCode, 'N/A');
        }
    }

    /**
     * Update customer group value. Method set group code instead id value
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setCustomerGroupValue()
    {
        $value = $this->getData(self::$groupAttributeCode);
        try {
            if (!isset(static::$customerGroupCodeById[$value])) {
                static::$customerGroupCodeById[$value] = $this->groupRepository->getById($value)->getCode();
            }
            $this->setCustomAttribute(self::$groupAttributeCode, static::$customerGroupCodeById[$value]);

        } catch (NoSuchEntityException $e) {
            $this->setCustomAttribute(self::$groupAttributeCode, 'N/A');
        }
    }

    /**
     * Update website value. Method set website name instead id value
     *
     * @return void
     */
    private function setWebsiteValue()
    {
        $value = $this->getData(self::$websiteAttributeCode);
        $list = $this->storeManager->getWebsites();
        $this->setCustomAttribute(self::$websiteAttributeCode, $list[$value]->getName());
        $this->setCustomAttribute(self::$websiteIdAttributeCode, $value);
    }

    /**
     * Update confirmation value. Method set confirmation text value to match what is shown in grid
     *
     * @return void
     */
    private function setConfirmationValue()
    {
        $value = $this->getData(self::$confirmationAttributeCode);
        $websiteId = $this->getData(self::$websiteIdAttributeCode) ?: $this->getData(self::$websiteAttributeCode);
        $isConfirmRequired = $this->scopeConfig->isSetFlag(
            AccountManagement::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $valueText = __('Confirmation Not Required');
        if ($isConfirmRequired) {
            $valueText = $value === null ? __('Confirmed') : __('Confirmation Required');
        }

        $this->setCustomAttribute(self::$confirmationAttributeCode, $valueText);
    }

    /**
     * Update lock expires value. Method set account lock text value to match what is shown in grid
     *
     * @return void
     * @throws Exception
     */
    private function setAccountLockValue()
    {
        $value = $this->getDataByPath(self::$accountLockAttributeCode);

        $valueText = __('Unlocked');
        if ($value !== null) {
            $lockExpires = new \DateTime($value);
            if ($lockExpires > new \DateTime()) {
                $valueText = __('Locked');
            }
        }

        $this->setCustomAttribute(self::$accountLockAttributeCode, $valueText);
    }
}
