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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration XML-files merger
 */
namespace Magento\Framework\Config;

abstract class AbstractXml
{
    /**
     * Data extracted from the merged configuration files
     *
     * @var array
     */
    protected $_data;

    /**
     * Dom configuration model
     * @var \Magento\Framework\Config\Dom
     */
    protected $_domConfig = null;

    /**
     * Instantiate with the list of files to merge
     *
     * @param array $configFiles
     * @throws \InvalidArgumentException
     */
    public function __construct($configFiles)
    {
        if (empty($configFiles)) {
            throw new \InvalidArgumentException('There must be at least one configuration file specified.');
        }
        $this->_data = $this->_extractData($this->_merge($configFiles));
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    abstract public function getSchemaFile();

    /**
     * Get absolute path to per-file XML-schema file
     *
     * @return string
     */
    public function getPerFileSchemaFile()
    {
        return null;
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    abstract protected function _extractData(\DOMDocument $dom);

    /**
     * Merge the config XML-files
     *
     * @param array $configFiles
     * @return \DOMDocument
     * @throws \Magento\Framework\Exception If a non-existing or invalid XML-file passed
     */
    protected function _merge($configFiles)
    {
        foreach ($configFiles as $key => $content) {
            try {
                $this->_getDomConfigModel()->merge($content);
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception("Invalid XML in file " . $key . ":\n" . $e->getMessage());
            }
        }
        if ($this->_isRuntimeValidated()) {
            $this->_performValidate();
        }
        return $this->_getDomConfigModel()->getDom();
    }

    /**
     * Perform xml validation
     *
     * @param string $file
     * @return $this
     * @throws \Magento\Framework\Exception If invalid XML-file passed
     */
    protected function _performValidate($file = null)
    {
        if (!$this->_getDomConfigModel()->validate($this->getSchemaFile(), $errors)) {
            $message = is_null($file) ? "Invalid Document \n" : "Invalid XML-file: {$file}\n";
            throw new \Magento\Framework\Exception($message . implode("\n", $errors));
        }
        return $this;
    }

    /**
     * Get if xml files must be runtime validated
     *
     * @return boolean
     */
    protected function _isRuntimeValidated()
    {
        return true;
    }

    /**
     * Get Dom configuration model
     *
     * @return \Magento\Framework\Config\Dom
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _getDomConfigModel()
    {
        if (is_null($this->_domConfig)) {
            $schemaFile = $this->getPerFileSchemaFile() &&
                $this->_isRuntimeValidated() ? $this->getPerFileSchemaFile() : null;
            $this->_domConfig = new \Magento\Framework\Config\Dom(
                $this->_getInitialXml(),
                $this->_getIdAttributes(),
                null,
                $schemaFile
            );
        }
        return $this->_domConfig;
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    abstract protected function _getInitialXml();

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    abstract protected function _getIdAttributes();
}
