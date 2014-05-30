<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_DijitMulti */
#require_once 'Zend/Dojo/Form/Element/DijitMulti.php';

/**
 * ComboBox dijit
 *
 * @uses       Zend_Dojo_Form_Element_DijitMulti
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ComboBox.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Element_ComboBox extends Zend_Dojo_Form_Element_DijitMulti
{
    /**
     * Use ComboBox dijit view helper
     * @var string
     */
    public $helper = 'ComboBox';

    /**
     * Flag: autoregister inArray validator?
     * @var bool
     */
    protected $_registerInArrayValidator = false;

    /**
     * Get datastore information
     *
     * @return array
     */
    public function getStoreInfo()
    {
        if (!$this->hasDijitParam('store')) {
            $this->dijitParams['store'] = array();
        }
        return $this->dijitParams['store'];
    }

    /**
     * Set datastore identifier
     *
     * @param  string $identifier
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setStoreId($identifier)
    {
        $store = $this->getStoreInfo();
        $store['store'] = (string) $identifier;
        $this->setDijitParam('store', $store);
        return $this;
    }

    /**
     * Get datastore identifier
     *
     * @return string|null
     */
    public function getStoreId()
    {
        $store = $this->getStoreInfo();
        if (array_key_exists('store', $store)) {
            return $store['store'];
        }
        return null;
    }

    /**
     * Set datastore dijit type
     *
     * @param  string $dojoType
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setStoreType($dojoType)
    {
        $store = $this->getStoreInfo();
        $store['type'] = (string) $dojoType;
        $this->setDijitParam('store', $store);
        return $this;
    }

    /**
     * Get datastore dijit type
     *
     * @return string|null
     */
    public function getStoreType()
    {
        $store = $this->getStoreInfo();
        if (array_key_exists('type', $store)) {
            return $store['type'];
        }
        return null;
    }

    /**
     * Set datastore parameters
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setStoreParams(array $params)
    {
        $store = $this->getStoreInfo();
        $store['params'] = $params;
        $this->setDijitParam('store', $store);
        return $this;
    }

    /**
     * Get datastore params
     *
     * @return array
     */
    public function getStoreParams()
    {
        $store = $this->getStoreInfo();
        if (array_key_exists('params', $store)) {
            return $store['params'];
        }
        return array();
    }

    /**
     * Set autocomplete flag
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_ComboBox
     */
    public function setAutocomplete($flag)
    {
        $this->setDijitParam('autocomplete', (bool) $flag);
        return $this;
    }

    /**
     * Get autocomplete flag
     *
     * @return bool
     */
    public function getAutocomplete()
    {
        if (!$this->hasDijitParam('autocomplete')) {
            return false;
        }
        return $this->getDijitParam('autocomplete');
    }

    /**
     * Is the value valid?
     *
     * @param  string $value
     * @param  mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $storeInfo = $this->getStoreInfo();
        if (!empty($storeInfo)) {
            $this->setRegisterInArrayValidator(false);
        }
        return parent::isValid($value, $context);
    }
}
