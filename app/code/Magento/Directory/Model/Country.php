<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Directory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Country model
 *
 * @method \Magento\Directory\Model\Resource\Country _getResource()
 * @method \Magento\Directory\Model\Resource\Country getResource()
 * @method string getCountryId()
 * @method \Magento\Directory\Model\Country setCountryId(string $value)
 * @method string getIso2Code()
 * @method \Magento\Directory\Model\Country setIso2Code(string $value)
 * @method string getIso3Code()
 * @method \Magento\Directory\Model\Country setIso3Code(string $value)
 */
namespace Magento\Directory\Model;

class Country extends \Magento\Core\Model\AbstractModel
{
    /**
     * @var array
     */
    static public $_format = array();

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Directory\Model\Country\FormatFactory
     */
    protected $_formatFactory;

    /**
     * @var \Magento\Directory\Model\Resource\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Directory\Model\Country\FormatFactory $formatFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Directory\Model\Country\FormatFactory $formatFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context, $registry, $resource, $resourceCollection, $data
        );
        $this->_locale = $locale;
        $this->_formatFactory = $formatFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Directory\Model\Resource\Country');
    }

    /**
     * @param string $code
     * @return $this
     */
    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        return $this;
    }

    /**
     * @return \Magento\Directory\Model\Resource\Region\Collection
     */
    public function getRegions()
    {
        return $this->getLoadedRegionCollection();
    }

    /**
     * @return \Magento\Directory\Model\Resource\Region\Collection
     */
    public function getLoadedRegionCollection()
    {
        $collection = $this->getRegionCollection();
        $collection->load();
        return $collection;
    }

    /**
     * @return \Magento\Directory\Model\Resource\Region\Collection
     */
    public function getRegionCollection()
    {
        $collection = $this->_regionCollectionFactory->create();
        $collection->addCountryFilter($this->getId());
        return $collection;
    }

    /**
     * @param \Magento\Object $address
     * @param bool $html
     * @return string
     */
    public function formatAddress(\Magento\Object $address, $html = false)
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

        $filter = new \Magento\Filter\Template\Simple();
        $addressText = $filter->setData($address->getData())->filter($template);

        if ($html) {
            $addressText = preg_replace('#(<br\s*/?>\s*){2,}#im', '<br/>', $addressText);
        } else {
            $addressText = preg_replace('#(\n\s*){2,}#m', "\n", $addressText);
        }

        return $addressText;
    }

    /**
     * Retrieve formats for
     *
     * @return \Magento\Directory\Model\Resource\Country\Format\Collection
     */
    public function getFormats()
    {
        if (!isset(self::$_format[$this->getId()]) && $this->getId()) {
            self::$_format[$this->getId()] = $this->_formatFactory->create()
                ->getCollection()->setCountryFilter($this)->load();
        }

        if (isset(self::$_format[$this->getId()])) {
            return self::$_format[$this->getId()];
        }

        return null;
    }

    /**
     * Retrieve format
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
     * @return string
     */
    public function getName()
    {
        if (!$this->getData('name')) {
            $this->setData('name', $this->_locale->getCountryTranslation($this->getId()));
        }
        return $this->getData('name');
    }

}
