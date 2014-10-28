<?php
/**
 * Factory to create new SoapServer objects.
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap\Server;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Webapi\Controller\Soap\Request\Handler
     */
    protected $_soapHandler;

    /**
     * Initialize the class
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Webapi\Controller\Soap\Request\Handler $soapHandler
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Webapi\Controller\Soap\Request\Handler $soapHandler
    ) {
        $this->_objectManager = $objectManager;
        $this->_soapHandler = $soapHandler;
    }

    /**
     * Create SoapServer
     *
     * @param string $url URL of a WSDL file
     * @param array $options Options including encoding, soap_version etc
     * @return \SoapServer
     */
    public function create($url, $options)
    {
        $soapServer = $this->_objectManager->create('SoapServer', array('wsdl' => $url, 'options' => $options));
        $soapServer->setObject($this->_soapHandler);
        return $soapServer;
    }
}
