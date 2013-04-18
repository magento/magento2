<?php
/**
 * Data source Repository
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Datasource_Repository
{
    protected $_dataSources = array();

    protected $_namespaces = array();

    public function addNameInNamespace($namespace, $name, $nameInNamespace)
    {
        if (isset($this->_namespaces[$namespace])) {
            $this->_namespaces[$namespace][$name] = $nameInNamespace;
        } else {
            $this->_namespaces[$namespace] = array($name => $nameInNamespace);
        }
        return $this;
    }

    public function getByNamespace($namespace)
    {
        if (!isset($this->_namespaces[$namespace])) {
            return array();
        }
        $dataSources = array();
        $dataSourcesNames = $this->_namespaces[$namespace];
        foreach ($dataSourcesNames as $name => $nameInNamespace) {
            $dataSources[$nameInNamespace] = $this->get($name);
        }
        return $dataSources;
    }

    public function add($name, $dataSource)
    {
        $this->_dataSources[$name] = $dataSource;
        return $this;
    }

    public function get($name)
    {
        if (!isset($this->_dataSources[$name])) {
            return null;
        }
        return $this->_dataSources[$name];
    }
}