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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect form date element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Form_Element_Date
    extends Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
{
    /**
     * Init multiline element
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('date');
    }

    /**
     * Required element attribute array
     *
     * @return array
     */
    public function getRequiredXmlAttributes()
    {
        return array(
            'label' => null,
            'type' => null,
            'format' => null
        );
    }

    /**
     * Add value to element
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    protected function _addValue(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        $values = $this->getEscapedValue();
        if (!empty($values)) {
            $valuesXmlObj = $xmlObj->addCustomChild('values');
            foreach ($values as $element => $config) {
                $valuesXmlObj->addCustomChild('item', null, array(
                    'id' => $config['id'],
                    'title' => $config['title'],
                    'label' => $config['label'],
                    'type' => $element,
                    'value' => $config['value']
                ));
            }
        }
        return $this;
    }
}
