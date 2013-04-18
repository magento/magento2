<?php
/**
 * Generic action controller for all resources available via web API.
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
abstract class Mage_Webapi_Controller_ActionAbstract
{
    /**#@+
     * Collection page sizes.
     */
    const PAGE_SIZE_DEFAULT = 10;
    const PAGE_SIZE_MAX = 100;
    /**#@-*/

    /**#@+
     * Allowed API resource methods.
     */
    const METHOD_CREATE = 'create';
    const METHOD_GET = 'get';
    const METHOD_LIST = 'list';
    const METHOD_UPDATE = 'update';
    const METHOD_DELETE = 'delete';
    const METHOD_MULTI_UPDATE = 'multiUpdate';
    const METHOD_MULTI_DELETE = 'multiDelete';
    const METHOD_MULTI_CREATE = 'multiCreate';
    /**#@-*/

    /** @var Mage_Webapi_Controller_Request */
    protected $_request;

    /** @var Mage_Webapi_Controller_Response */
    protected $_response;

    /** @var Mage_Webapi_Helper_Data */
    protected $_translationHelper;

    /** @var Mage_Core_Model_Factory_Helper */
    protected $_helperFactory;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Request_Factory $requestFactory
     * @param Mage_Webapi_Controller_Response_Factory $responseFactory
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     */
    public function __construct(
        Mage_Webapi_Controller_Request_Factory $requestFactory,
        Mage_Webapi_Controller_Response_Factory $responseFactory,
        Mage_Core_Model_Factory_Helper $helperFactory
    ) {
        $this->_helperFactory = $helperFactory;
        $this->_translationHelper = $this->_helperFactory->get('Mage_Webapi_Helper_Data');
        $this->_request = $requestFactory->get();
        $this->_response = $responseFactory->get();
    }

    /**
     * Retrieve request.
     *
     * @return Mage_Webapi_Controller_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response.
     *
     * @return Mage_Webapi_Controller_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set navigation parameters and apply filters from URL params.
     *
     * @param Varien_Data_Collection_Db $collection
     * @return Varien_Data_Collection_Db
     * @throws Mage_Webapi_Exception
     */
    // TODO: Check and finish this method (the implementation was migrated from Magento 1)
    final protected function _applyCollectionModifiers(Varien_Data_Collection_Db $collection)
    {
        $pageNumber = $this->getRequest()->getPageNumber();
        if ($pageNumber != abs($pageNumber)) {
            throw new Mage_Webapi_Exception(
                $this->_translationHelper->__("Page number is invalid."),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        }
        $pageSize = $this->getRequest()->getPageSize();
        if (null == $pageSize) {
            $pageSize = self::PAGE_SIZE_DEFAULT;
        } else {
            if ($pageSize != abs($pageSize) || $pageSize > self::PAGE_SIZE_MAX) {
                throw new Mage_Webapi_Exception(
                    $this->_translationHelper->__('The paging limit exceeds the allowed number.'),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST
                );
            }
        }
        $orderField = $this->getRequest()->getOrderField();
        if (null !== $orderField) {
            if (!is_string($orderField)
                // TODO: Check if order field is allowed for specified entity
            ) {
                throw new Mage_Webapi_Exception(
                    $this->_translationHelper->__('Collection "order" value is invalid.'),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST
                );
            }
            $collection->setOrder($orderField, $this->getRequest()->getOrderDirection());
        }
        $collection->setCurPage($pageNumber)->setPageSize($pageSize);
        return $collection;
    }

    /**
     * Check if specified action is defined in current controller.
     *
     * @param string $actionName
     * @return bool
     */
    public function hasAction($actionName)
    {
        return method_exists($this, $actionName);
    }

    /**
     * Retrieve list of allowed method names in action controllers.
     *
     * @return array
     */
    public static function getAllowedMethods()
    {
        return array(
            self::METHOD_CREATE,
            self::METHOD_GET,
            self::METHOD_LIST,
            self::METHOD_UPDATE,
            self::METHOD_MULTI_UPDATE,
            self::METHOD_DELETE,
            self::METHOD_MULTI_DELETE,
            self::METHOD_MULTI_CREATE,
        );
    }
}
