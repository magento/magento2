<?php
/**
 * DataService graph manages creation and storage of data services.
 *
 * manages the graph of objects
 *  - initializes data service
 *  - calls factory to retrieve data
 *  - stores data to repository
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

class Graph implements \Magento\Core\Model\DataService\Path\NodeInterface
{
    /** @var \Magento\Core\Model\DataService\Invoker */
    protected $_invoker;

    /** @var \Magento\Core\Model\DataService\Repository */
    protected $_repository;

    /**
     * @param \Magento\Core\Model\DataService\Invoker $dataServiceInvoker
     * @param \Magento\Core\Model\DataService\Repository $repository
     */
    public function __construct(
        \Magento\Core\Model\DataService\Invoker $dataServiceInvoker,
        \Magento\Core\Model\DataService\Repository $repository
    ) {
        $this->_invoker = $dataServiceInvoker;
        $this->_repository = $repository;
    }

    /**
     * Get the value for the method argument
     *
     * @param string $path
     * @return mixed
     */
    public function getArgumentValue($path)
    {
        return $this->_invoker->getArgumentValue($path);
    }

    /**
     * Takes array of the following structure
     * and initializes all of the data sources
     *
     *  array(dataServiceName => array(
     *      blocks => array(
     *          'namespace' => aliasInNamespace
     *      ))
     *
     * @param array $dataServicesList
     * @return \Magento\Core\Model\DataService\Graph
     * @throws \InvalidArgumentException
     */
    public function init(array $dataServicesList)
    {
        foreach ($dataServicesList as $dataServiceName => $namespaceConfig) {
            if (!isset($namespaceConfig['namespaces'])) {
                throw new \InvalidArgumentException("Data reference configuration doesn't have a block to link to");
            }
            if ($this->get($dataServiceName) === false) {
                throw new \InvalidArgumentException("Data service '$dataServiceName' couldn't be retrieved");
            }
            foreach ($namespaceConfig['namespaces'] as $namespaceName => $aliasInNamespace) {
                $this->_repository->setAlias($namespaceName, $dataServiceName, $aliasInNamespace);
            }
        }
        return $this;
    }

    /**
     * Retrieve the data or the service call based on its name
     *
     * @param string $dataServiceName
     * @return bool|array
     */
    public function get($dataServiceName)
    {
        $dataService = $this->_repository->get($dataServiceName);
        if ($dataService === null) {
            $dataService = $this->_invoker->getServiceData($dataServiceName);
            $this->_repository->add($dataServiceName, $dataService);
        }
        return $dataService;
    }

    /**
     * Retrieve all data for the service calls for particular namespace.
     *
     * @param string $namespace
     * @return mixed
     */
    public function getByNamespace($namespace)
    {
        return $this->_repository->getByNamespace($namespace);
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
