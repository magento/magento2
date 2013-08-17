<?php
/**
 * Wrapper around Mage_Core_Controller_Request_Http for the Navigator class.
 *
 * HTTP Requests need to be exposed as data services for the front end (twig) to be able to access the
 * request data. This class acts as a wrapper around the Mage_Core_Controller_Request_Http object so
 * that the data can be searched for and extracted via the Navigator class.
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
class Mage_Core_Model_DataService_Path_Request implements Mage_Core_Model_DataService_Path_NodeInterface
{
    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function __construct(Mage_Core_Controller_Request_Http $request)
    {
        $this->_request = $request;
    }

    /**
     * Return a child path node that corresponds to the input path element.  This can be used to walk the
     * data service graph.  Leaf nodes in the graph tend to be of mixed type (scalar, array, or object).
     *
     * @param string $pathElement the path element name of the child node
     * @return Mage_Core_Model_DataService_Path_NodeInterface|mixed|null the child node, or mixed if this is a leaf node
     */
    public function getChildNode($pathElement)
    {
        switch ($pathElement) {
            case 'params':
                return $this->_request->getParams();
        }

        return null;
    }
}