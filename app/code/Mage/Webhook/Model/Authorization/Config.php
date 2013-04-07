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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Webhook Acl Config model
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Webhook_Model_Authorization_Config implements Mage_Core_Model_Acl_Config_ConfigInterface
{

    const ACL_VIRTUAL_RESOURCES_XPATH = '/config/*';

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Magento_Acl_Config_Reader
     */
    protected $_reader;

    /**
     * @var Mage_Webhook_Model_Authorization_Config_Reader_Factory
     */
    protected $_readerFactory;

    /**
     * Module configuration reader
     *
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Mage_Webhook_Model_Authorization_Config_Reader_Factory $readerFactory
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Mage_Webhook_Model_Authorization_Config_Reader_Factory $readerFactory
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_readerFactory = $readerFactory;
    }

    /**
     * Retrieve list of acl files from each module
     *
     * @return array
     */
    protected function _getAclResourceFiles()
    {
        $files = $this->_moduleReader
            ->getModuleConfigurationFiles('webhook' . DIRECTORY_SEPARATOR . 'acl.xml');
        return (array)$files;
    }

    /**
     * Reader object initialization.
     *
     * @return Magento_Acl_Config_Reader
     */
    protected function _getReader()
    {
        if (is_null($this->_reader)) {
            $aclResourceFiles = $this->_getAclResourceFiles();
            $this->_reader = $this->_readerFactory->createReader(array('configFiles'  => $aclResourceFiles));
        }
        return $this->_reader;
    }


    /**
     * Get DOMXPath with loaded resources inside
     *
     * @return DOMXPath
     */
    protected function _getXPathResources()
    {
        $aclResources = $this->_getReader()->getAclResources();
        return new DOMXPath($aclResources);
    }

    /**
     * Return ACL Resources
     *
     * @return DOMNodeList
     */
    public function getAclResources()
    {
        // We don't have any resources that aren't virtual
        return null;
    }

    /**
     * Return ACL Virtual Resources
     *
     * Virtual resources are not shown in resource list, they use existing resource to check permission
     *
     * @return DOMNodeList
     */
    public function getAclVirtualResources()
    {
        return $this->_getXPathResources()->query(self::ACL_VIRTUAL_RESOURCES_XPATH);
    }

    /**
     * Return the parent of the given topic
     * @param $topic_id
     * @return string
     */
    public function getParentFromTopic( $topic_id) {
        $topics = $this->_getXPathResources()->query("//topic[@id='" . $topic_id . "']/../@id");
        return $topics->item(0)->nodeValue;
    }
}
