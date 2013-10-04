<?php
/**
 * Magento Data Service Config reader
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
namespace Magento\Core\Model\DataService\Config;

class Reader extends \Magento\Config\AbstractXml
{
    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    private $_modulesReader;

    /**
     * @param \Magento\Core\Model\Config\Modules\Reader $moduleReader
     * @param array $configFiles
     */
    public function __construct(
        \Magento\Core\Model\Config\Modules\Reader $moduleReader,
        array $configFiles
    ) {
        if (count($configFiles)) {
            parent::__construct($configFiles);
        }
        $this->_modulesReader = $moduleReader;
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->_modulesReader->getModuleDir('etc', 'Magento_Core') . '/service_calls.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _extractData(\DOMDocument $dom)
    {
        return array();
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0"?><service_calls></service_calls>';
    }

    /**
     * Get if xml files must be runtime validated
     *
     * @return boolean
     */
    protected function _isRuntimeValidated()
    {
        return false;
    }

    /**
     * Retrieve Service Calls
     *
     * @return \DOMDocument
     */
    public function getServiceCallConfig()
    {
        return $this->_getDomConfigModel()->getDom();
    }

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array(
            '/service_calls/service_call/arg' => 'name',
            '/service_calls/service_call' => 'name',
        );
    }

    /**
     * Perform xml validation
     *
     * @return \Magento\Config\AbstractXml
     * @throws \Magento\Exception if invalid XML-file passed
     */
    public function validate()
    {
        return $this->_performValidate();
    }
}
