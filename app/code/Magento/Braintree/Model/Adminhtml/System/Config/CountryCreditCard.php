<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adminhtml\System\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CountryCreditCard
 */
class CountryCreditCard extends Value
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];
        foreach ($value as $data) {
            if (empty($data['country_id']) || empty($data['cc_types'])) {
                continue;
            }
            $country = $data['country_id'];
            if (array_key_exists($country, $result)) {
                $result[$country] = $this->appendUniqueCountries($result[$country], $data['cc_types']);
            } else {
                $result[$country] = $data['cc_types'];
            }
        }
        $this->setValue($this->serializer->serialize($result));
        return $this;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        $value = $this->serializer->unserialize($this->getValue());
        if (is_array($value)) {
            $value = $this->encodeArrayFieldValue($value);
            $this->setValue($value);
        }
        return $this;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $country => $creditCardType) {
            $id = $this->mathRandom->getUniqueHash('_');
            $result[$id] = ['country_id' => $country, 'cc_types' => $creditCardType];
        }
        return $result;
    }

    /**
     * Append unique countries to list of exists and reindex keys
     *
     * @param array $countriesList
     * @param array $inputCountriesList
     * @return array
     */
    private function appendUniqueCountries(array $countriesList, array $inputCountriesList)
    {
        $result = array_merge($countriesList, $inputCountriesList);
        return array_values(array_unique($result));
    }
}
