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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Controller_Front_Router
{
    protected $_config = null;
    
    public function __construct($config)
    {
        $this->_config = $config;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function addRoutes(Zend_Controller_Router_Interface $router)
    {
        $frontName = $this->_config->getName();
        $routeMatch = $frontName.'/:controller/:action/*';
        $moduleName = (string)$this->_config->module;
        $routeParams = array('module'=>$moduleName, 'controller'=>'index', 'action'=>'index', '_frontName'=>$frontName);
        $route = new Zend_Controller_Router_Route($routeMatch, $routeParams);
        $router->addRoute($moduleName, $route);
        
        return $this;
    }
    
    public function getUrl($params=array())
    {
        static $reservedKeys = array('module'=>1, 'controller'=>1, 'action'=>1, 'array'=>1);
        
        if (is_string($params)) {
            $paramsArr = explode('/', $params);
            $params = array('controller'=>$paramsArr[0], 'action'=>$paramsArr[1]);
        }
        
        $url = Mage::getBaseUrl($params);

        if (!empty($params['frontName'])) {
            $url .= $params['frontName'].'/';
        } else {
            $url .= $this->_config->getName().'/';
        }
        
        if (!empty($params)) {
            $paramsStr = '';
            foreach ($params as $key=>$value) {
                if (!isset($reservedKeys[$key]) && '_'!==$key{0} && !empty($value)) {
                    $paramsStr .= $key.'/'.$value.'/';
                }
            }
            
            if (empty($params['controller']) && !empty($paramsStr)) {
                $params['controller'] = 'index';
            }
            $url .= empty($params['controller']) ? '' : $params['controller'].'/';
            
            if (empty($params['action']) && !empty($paramsStr)) {
                $params['action'] = 'index';
            }
            $url .= empty($params['action']) ? '' : $params['action'].'/';
            
            $url .= $paramsStr;
            
            $url .= empty($params['array']) ? '' : '?' . http_build_query($params['array']);
        }

        return $url;
    }
}
