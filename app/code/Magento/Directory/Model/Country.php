<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Directory\Model;

/**
 * Country model
 *
 * @method \Magento\Directory\Model\ResourceModel\Country _getResource()
 * @method \Magento\Directory\Model\ResourceModel\Country getResource()
 * @method string getCountryId()
 * @method \Magento\Directory\Model\Country setCountryId(string $value)
 *
 * @api
 */
class Country extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var array
     */
    public static $_format = [];

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \Magento\Directory\Model\Country\FormatFactory
     */
    protected $_formatFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param Country\FormatFactory $formatFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Directory\Model\Country\FormatFactory $formatFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_localeLists = $localeLists;
        $this->_formatFactory = $formatFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Directory\Model\ResourceModel\Country::class);
    }

    /**
     * Load country by code
     *
     * @param string $code
     * @return $this
     */
    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        return $this;
    }

    /**
     * Get regions
     *
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getRegions()
    {
        return $this->getLoadedRegionCollection();
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getLoadedRegionCollection()
    {
        $collection = $this->getRegionCollection();
        $collection->load();
        return $collection;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getRegionCollection()
    {
        $collection = $this->_regionCollectionFactory->create();
        $collection->addCountryFilter($this->getId());
        return $collection;
    }

    /**
     * @param \Magento\Framework\DataObject $address
     * @param bool $html
     * @return string
     */
    public function formatAddress(\Magento\Framework\DataObject $address, $html = false)
    {
        //TODO: is it still used?
        $address->getRegion();
        $address->getCountry();

        $template = $this->getData('address_template_' . ($html ? 'html' : 'plain'));
        if (empty($template)) {
            if (!$this->getId()) {
                $template = '{{firstname}} {{lastname}}';
            } elseif (!$html) {
                $template = "{{firstname}} {{lastname}}
{{company}}
{{street1}}
{{street2}}
{{city}}, {{region}} {{postcode}}";
            } else {
                $template = "{{firstname}} {{lastname}}<br/>
{{street}}<br/>
{{city}}, {{region}} {{postcode}}<br/>
T: {{telephone}}";
            }
        }

        $filter = new \Magento\Framework\Filter\Template\Simple();
        $addressText = $filter->setData($address->getData())->filter($template);

        if ($html) {
            $addressText = preg_replace('#(<br\s*/?>\s*){2,}#im', '<br/>', $addressText);
        } else {
            $addressText = preg_replace('#(\n\s*){2,}#m', "\n", $addressText);
        }

        return $addressText;
    }

    /**
     * Retrieve country formats
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Format\Collection
     */
    public function getFormats()
    {
        if (!isset(self::$_format[$this->getId()]) && $this->getId()) {
            self::$_format[$this->getId()] = $this->_formatFactory->create()->getCollection()->setCountryFilter(
                $this
            )->load();
        }

        if (isset(self::$_format[$this->getId()])) {
            return self::$_format[$this->getId()];
        }

        return null;
    }

    /**
     * Retrieve country format
     *
     * @param string $type
     * @return \Magento\Directory\Model\Country\Format|null
     */
    public function getFormat($type)
    {
        if ($this->getFormats()) {
            foreach ($this->getFormats() as $format) {
                if ($format->getType() == $type) {
                    return $format;
                }
            }
        }
        return null;
    }

    /**
     * Get country name
     *
     * @return string
     */
    public function getName($locale = null)
    {
        if ($locale == null) {
            $cache_key = 'name_default';
        } else {
            $cache_key = 'name_' . $locale;
        }

        if (!$this->getData($cache_key)) {
            $this->setData($cache_key, $this->_localeLists->getCountryTranslation($this->getId(), $locale));
        }
        return $this->getData($cache_key);
    }
}
