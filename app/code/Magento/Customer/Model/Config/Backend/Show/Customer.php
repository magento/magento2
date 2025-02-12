<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Config\Backend\Show;

use Magento\Config\App\Config\Source\ModularConfigSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Customer Show Customer Model
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class Customer extends \Magento\Framework\App\Config\Value
{
    public const XML_PATH_CUSTOMER_ADDRESS_SHOW_COMPANY = 'customer/address/company_show';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    private $telephoneShowDefaultValue = 'req';

    /**
     * @var ModularConfigSource
     */
    private $configSource;

    /**
     * @var array
     */
    private $valueConfig = [
        '' => ['is_required' => 0, 'is_visible' => 0],
        'opt' => ['is_required' => 0, 'is_visible' => 1],
        '1' => ['is_required' => 0, 'is_visible' => 1],
        'req' => ['is_required' => 1, 'is_visible' => 1],
    ];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param ModularConfigSource|null $configSource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?ModularConfigSource $configSource = null
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
        $this->configSource = $configSource ?: ObjectManager::getInstance()->get(ModularConfigSource::class);
    }

    /**
     * Retrieve attribute code
     *
     * @return string
     */
    protected function _getAttributeCode()
    {
        return $this->getField() === null ? '' : str_replace('_show', '', $this->getField());
    }

    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     */
    protected function _getAttributeObjects()
    {
        return [$this->_eavConfig->getAttribute('customer', $this->_getAttributeCode())];
    }

    /**
     * Actions after save
     *
     * @return $this
     */
    public function afterSave()
    {
        $result = parent::afterSave();

        $value = $this->getValue();
        $data = $this->getValueConfig($value);
        if ($this->getScope() == 'websites') {
            $website = $this->storeManager->getWebsite($this->getScopeCode());
            $dataFieldPrefix = 'scope_';
        } else {
            $website = null;
            $dataFieldPrefix = '';
        }

        foreach ($this->_getAttributeObjects() as $attributeObject) {
            if ($website) {
                $attributeObject->setWebsite($website);
                $attributeObject->load($attributeObject->getId());
            }
            $attributeObject->setData($dataFieldPrefix . 'is_required', $data['is_required']);
            $attributeObject->setData($dataFieldPrefix . 'is_visible', $data['is_visible']);
            $attributeObject->save();
        }

        return $result;
    }

    /**
     * Processing object after delete data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDelete()
    {
        $result = parent::afterDelete();

        if ($this->getScope() == 'websites') {
            $website = $this->storeManager->getWebsite($this->getScopeCode());
            foreach ($this->_getAttributeObjects() as $attributeObject) {
                $attributeObject->setWebsite($website);
                $attributeObject->load($attributeObject->getId());
                $attributeObject->setData('scope_is_required', null);
                $attributeObject->setData('scope_is_visible', null);
                $attributeObject->save();
            }
        } elseif ($this->getScope() == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $defaultValue = $this->configSource->get(ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '/' . $this->getPath());
            $valueConfig = $this->getValueConfig($defaultValue === [] ? '' : $defaultValue);
            foreach ($this->_getAttributeObjects() as $attributeObject) {
                $attributeObject->setData('is_required', $valueConfig['is_required']);
                $attributeObject->setData('is_visible', $valueConfig['is_visible']);
                $attributeObject->save();
            }
        }

        return $result;
    }

    /**
     * Get value config
     *
     * @param string|int $value
     * @return array
     */
    private function getValueConfig($value): array
    {
        if (isset($this->valueConfig[$value])) {
            $config = $this->valueConfig[$value];
        } else {
            $config = $this->valueConfig[''];
        }
        return $config;
    }
}
