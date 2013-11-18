<?php
/**
 * DataService Repository stores the data and allows to retrieve for service calls.
 *
 * Assigns namespaces and aliases to the service calls data.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService;

class Repository implements \Magento\Core\Model\DataService\Path\NodeInterface
{
    /**
     * @var array
     */
    protected $_serviceData = array();

    /**
     * @var array
     */
    protected $_namespaces = array();

    /**
     * Assign a new name to existing namespace identified by alias.
     *
     * @param string $namespace
     * @param string $serviceName
     * @param string $alias
     * @return $this
     */
    public function setAlias($namespace, $serviceName, $alias)
    {
        if (isset($this->_namespaces[$namespace])) {
            $this->_namespaces[$namespace][$serviceName] = $alias;
        } else {
            $this->_namespaces[$namespace] = array($serviceName => $alias);
        }
        return $this;
    }

    /**
     * Get all data services from namespace.
     *
     * @param string $namespace
     * @return array
     */
    public function getByNamespace($namespace)
    {
        if (!isset($this->_namespaces[$namespace])) {
            return array();
        }
        $dataServices = array();
        $dataServicesNames = $this->_namespaces[$namespace];
        foreach ($dataServicesNames as $serviceName => $alias) {
            $dataServices[$alias] = $this->get($serviceName);
        }
        return $dataServices;
    }

    /**
     * Add new service data.
     *
     * @param string $serviceName
     * @param array $data
     * @return \Magento\Core\Model\DataService\Repository
     */
    public function add($serviceName, $data)
    {
        $this->_serviceData[$serviceName] = $data;
        return $this;
    }

    /**
     * Get service data by name.
     *
     * @param string $serviceName
     * @return array|null
     */
    public function get($serviceName)
    {
        if (!isset($this->_serviceData[$serviceName])) {
            return null;
        }
        return $this->_serviceData[$serviceName];
    }

    /**
     * Return a child path node that corresponds to the input path element.  This can be used to walk the
     * data service graph.  Leaf nodes in the graph tend to be of mixed type (scalar, array, or object).
     *
     * @param string $pathElement the path element name of the child node
     * @return \Magento\Core\Model\DataService\Path\NodeInterface|mixed|null the child node,
     *    or mixed if this is a leaf node
     */
    public function getChildNode($pathElement)
    {
        return $this->get($pathElement);
    }
}
