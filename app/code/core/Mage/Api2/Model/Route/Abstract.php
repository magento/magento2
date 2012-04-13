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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice apia2 route abstract
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Api2_Model_Route_Abstract extends Zend_Controller_Router_Route
{
    /**#@+
     * Names for Zend_Controller_Router_Route::__construct params
     */
    const PARAM_ROUTE      = 'route';
    const PARAM_DEFAULTS   = 'defaults';
    const PARAM_REQS       = 'reqs';
    const PARAM_TRANSLATOR = 'translator';
    const PARAM_LOCALE     = 'locale';
    /**#@- */

    /*
     * Default values of parent::__construct() params
     *
     * @var array
     */
    protected $_paramsDefaultValues = array(
        self::PARAM_ROUTE      => null,
        self::PARAM_DEFAULTS   => array(),
        self::PARAM_REQS       => array(),
        self::PARAM_TRANSLATOR => null,
        self::PARAM_LOCALE     => null
    );

    /**
     * Process construct param and call parent::__construct() with params
     *
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        parent::__construct(
            $this->_getArgumentValue(self::PARAM_ROUTE, $arguments),
            $this->_getArgumentValue(self::PARAM_DEFAULTS, $arguments),
            $this->_getArgumentValue(self::PARAM_REQS, $arguments),
            $this->_getArgumentValue(self::PARAM_TRANSLATOR, $arguments),
            $this->_getArgumentValue(self::PARAM_LOCALE, $arguments)
        );
    }

    /**
     * Retrieve argument value
     *
     * @param string $name argument name
     * @param array $arguments
     * @return mixed
     */
    protected function _getArgumentValue($name, array $arguments)
    {
        return isset($arguments[$name]) ? $arguments[$name] : $this->_paramsDefaultValues[$name];
    }

    /**
     * Matches a Request with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param Mage_Api2_Model_Request $request
     * @param boolean $partial Partial path matching
     * @return array|bool An array of assigned values or a boolean false on a mismatch
     */
    public function match($request, $partial = false)
    {
        return parent::match(ltrim($request->getPathInfo(), $this->_urlDelimiter), $partial);
    }
}
