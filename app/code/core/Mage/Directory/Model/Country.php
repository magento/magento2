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
 * @category    Mage
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Country model
 *
 * @method Mage_Directory_Model_Resource_Country _getResource()
 * @method Mage_Directory_Model_Resource_Country getResource()
 * @method string getCountryId()
 * @method Mage_Directory_Model_Country setCountryId(string $value)
 * @method string getIso2Code()
 * @method Mage_Directory_Model_Country setIso2Code(string $value)
 * @method string getIso3Code()
 * @method Mage_Directory_Model_Country setIso3Code(string $value)
 *
 * @category    Mage
 * @package     Mage_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Directory_Model_Country extends Mage_Core_Model_Abstract
{
    static public $_format = array();

    protected function _construct()
    {
        $this->_init('Mage_Directory_Model_Resource_Country');
    }

    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        return $this;
    }

    public function getRegions()
    {
        return $this->getLoadedRegionCollection();
    }

    public function getLoadedRegionCollection()
    {
        $collection = $this->getRegionCollection();
        $collection->load();
        return $collection;
    }

    public function getRegionCollection()
    {
        $collection = Mage::getResourceModel('Mage_Directory_Model_Resource_Region_Collection');
        $collection->addCountryFilter($this->getId());
        return $collection;
    }

    public function formatAddress(Varien_Object $address, $html=false)
    {
        //TODO: is it still used?
        $address->getRegion();
        $address->getCountry();



        $template = $this->getData('address_template_'.($html ? 'html' : 'plain'));
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

        $filter = new Varien_Filter_Template_Simple();
        $addressText = $filter->setData($address->getData())->filter($template);

        if ($html) {
            $addressText = preg_replace('#(<br\s*/?>\s*){2,}#im', '<br/>', $addressText);
        } else {
            $addressText = preg_replace('#(\n\s*){2,}#m', "\n", $addressText);
        }

        return $addressText;
    }

    /**
     * Retrive formats for
     *
     * @return Mage_Directory_Model_Resource_Country_Format_Collection
     */
    public function getFormats()
    {
        if (!isset(self::$_format[$this->getId()]) && $this->getId()) {
            self::$_format[$this->getId()] = Mage::getModel('Mage_Directory_Model_Country_Format')
                                                ->getCollection()
                                                ->setCountryFilter($this)
                                                ->load();
        }

        if (isset(self::$_format[$this->getId()])) {
            return self::$_format[$this->getId()];
        }

        return null;
    }

    /**
     * Retrive format
     *
     * @param string $type
     * @return Mage_Directory_Model_Country_Format
     */
    public function getFormat($type)
    {
        if ($this->getFormats()) {
            foreach ($this->getFormats() as $format) {
                if ($format->getType()==$type) {
                    return $format;
                }
            }
        }
        return null;
    }

    public function getName()
    {
        if(!$this->getData('name')) {
            $this->setData(
                'name',
                Mage::app()->getLocale()->getCountryTranslation($this->getId())
            );
        }
        return $this->getData('name');
    }

}
