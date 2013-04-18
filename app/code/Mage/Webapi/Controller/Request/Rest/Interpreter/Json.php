<?php
/**
 * JSON interpreter of REST request content.
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
class Mage_Webapi_Controller_Request_Rest_Interpreter_Json implements
    Mage_Webapi_Controller_Request_Rest_InterpreterInterface
{
    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /** @var Mage_Core_Model_Factory_Helper */
    protected $_helperFactory;

    /** @var Mage_Core_Model_App */
    protected $_app;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $app
     */
    public function __construct(Mage_Core_Model_Factory_Helper $helperFactory, Mage_Core_Model_App $app)
    {
        $this->_helperFactory = $helperFactory;
        $this->_helper = $this->_helperFactory->get('Mage_Webapi_Helper_Data');
        $this->_app = $app;
    }

    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     * @return array|null Return NULL if content is invalid.
     * @throws InvalidArgumentException
     * @throws Mage_Webapi_Exception If decoding error was encountered.
     */
    public function interpret($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" data type is invalid. String is expected.',
                gettype($encodedBody)
            ));
        }
        try {
            /** @var Mage_Core_Helper_Data $jsonHelper */
            $jsonHelper = $this->_helperFactory->get('Mage_Core_Helper_Data');
            $decodedBody = $jsonHelper->jsonDecode($encodedBody);
        } catch (Zend_Json_Exception $e) {
            if (!$this->_app->isDeveloperMode()) {
                throw new Mage_Webapi_Exception($this->_helper->__('Decoding error.'),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST);
            } else {
                throw new Mage_Webapi_Exception(
                    'Decoding error: ' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST
                );
            }

        }
        return $decodedBody;
    }
}
