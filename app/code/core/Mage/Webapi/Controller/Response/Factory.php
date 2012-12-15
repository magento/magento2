<?php
/**
 * Factory of web API requests.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Response_Factory
{
    /**
     * List of response classes corresponding to API types.
     *
     * @var array
     */
    protected $_apiResponseMap = array(
        Mage_Webapi_Controller_Front::API_TYPE_REST => 'Mage_Webapi_Controller_Response_Rest',
        Mage_Webapi_Controller_Front::API_TYPE_SOAP => 'Mage_Webapi_Controller_Response',
    );

    /** @var Magento_ObjectManager */
    protected $_objectManager;

    /** @var Mage_Webapi_Controller_Front */
    protected $_apiFrontController;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Front $apiFrontController
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(
        Mage_Webapi_Controller_Front $apiFrontController,
        Magento_ObjectManager $objectManager
    ) {
        $this->_apiFrontController = $apiFrontController;
        $this->_objectManager = $objectManager;
    }

    /**
     * Create response object.
     *
     * Use current API type to define proper response class.
     *
     * @return Mage_Webapi_Controller_Response
     * @throws LogicException If there is no corresponding response class for current API type.
     */
    public function get()
    {
        $apiType = $this->_apiFrontController->determineApiType();
        if (!isset($this->_apiResponseMap[$apiType])) {
            throw new LogicException(
                sprintf('There is no corresponding response class for the "%s" API type.', $apiType)
            );
        }
        $requestClass = $this->_apiResponseMap[$apiType];
        return $this->_objectManager->get($requestClass);
    }
}
