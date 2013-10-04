<?php
/**
 * This class reads config.xml of modules, and provides interface to the configuration of service calls.
 *
 * Service calls are defined in service_calls.xml files in etc directory of the modules.
 * Additionally, reference to service_calls.xml file is configured in config.xml file.
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

class Config implements \Magento\Core\Model\DataService\ConfigInterface
{
    /** Xpath to service call */
    const SERVICE_CALLS_XPATH = '/service_calls/service_call';

    /** @var \Magento\Core\Model\DataService\Config\Reader\Factory */
    protected $_readerFactory;

    /** @var  \Magento\Core\Model\DataService\Config\Reader */
    protected $_reader;

    /** @var array $_serviceCallNodes */
    protected $_serviceCallNodes;

    /** @var \Magento\Core\Model\Config\Modules\Reader  */
    protected $_moduleReader;

    /**
     * @param \Magento\Core\Model\DataService\Config\Reader\Factory $readerFactory
     * @param \Magento\Core\Model\Config\Modules\Reader
     */
    public function __construct(\Magento\Core\Model\DataService\Config\Reader\Factory $readerFactory,
        \Magento\Core\Model\Config\Modules\Reader $moduleReader
    ) {
        $this->_readerFactory = $readerFactory;
        $this->_moduleReader = $moduleReader;
        $this->_indexServiceCallNodes();
    }

    /**
     * Build an index of service calls nodes to avoid expensive xpath calls
     *
     * @return \Magento\Core\Model\DataService\Config $this
     */
    private function _indexServiceCallNodes()
    {
        /** @var \DOMElement $node */
        foreach ($this->getServiceCalls() as $node) {
            $this->_serviceCallNodes[$node->getAttribute('name')] = $node;
        }
        return $this;
    }

    /**
     * Reader object initialization.
     *
     * @return \Magento\Core\Model\DataService\Config\Reader
     */
    protected function _getReader()
    {
        if (is_null($this->_reader)) {
            $serviceCallsFiles = $this->_getServiceCallsFiles();
            $this->_reader = $this->_readerFactory->createReader($serviceCallsFiles);
        }
        return $this->_reader;
    }

    /**
     * Retrieve list of service calls files from each module.
     *
     * @return array
     */
    protected function _getServiceCallsFiles()
    {
        return $this->_moduleReader->getConfigurationFiles('service_calls.xml');
    }

    /**
     * Get \DOMXPath with loaded service calls inside.
     *
     * @return \DOMXPath
     */
    protected function _getXPathServiceCalls()
    {
        $serviceCalls = $this->_getReader()->getServiceCallConfig();
        return new \DOMXPath($serviceCalls);
    }

    /**
     * Return Service Calls.
     *
     * @return \DOMNodeList
     */
    public function getServiceCalls()
    {
        return $this->_getXPathServiceCalls()->query(self::SERVICE_CALLS_XPATH);
    }

    /**
     * Get the class information for a given service call
     *
     * @param string $alias
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getClassByAlias($alias)
    {
        //validate that service call is defined
        if (!isset($this->_serviceCallNodes[$alias])) {
            throw new \InvalidArgumentException("Service call with name '{$alias}'  doesn't exist");
        }

        /** @var \DOMElement $node */
        $node = $this->_serviceCallNodes[$alias];
        $methodArguments = array();

        /** @var \DOMElement $child */
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $methodArguments[$child->getAttribute('name')] = $child->nodeValue;
            }
        }

        $result = array(
            'class' => $node->getAttribute('service'),
            'retrieveMethod' => $node->getAttribute('method'),
            'methodArguments' => $methodArguments,
        );

        //validate that service attribute is defined
        if (!$result['class']) {
            throw new \InvalidArgumentException("Invalid Service call {$alias}, "
                . 'service type must be defined in the "service" attribute');
        }

        //validate that retrieval method attribute is defined
        if (!$result['retrieveMethod']) {
            throw new \LogicException("Invalid Service call {$alias}, "
                . "retrieval method must be defined for the service {$result['class']}");
        }

        return $result;
    }
}
