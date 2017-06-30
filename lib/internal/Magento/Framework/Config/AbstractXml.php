<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configuration XML-files merger
 */
namespace Magento\Framework\Config;

/**
 * @api
 */
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
     * @var \Magento\Framework\Config\DomFactory
     */
    protected $domFactory;

    /**
     * Instantiate with the list of files to merge
     *
     * @param array $configFiles
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $configFiles,
        \Magento\Framework\Config\DomFactory $domFactory
    ) {
        $this->domFactory = $domFactory;
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
     * @throws \Magento\Framework\Exception\LocalizedException If a non-existing or invalid XML-file passed
     */
    protected function _merge($configFiles)
    {
        foreach ($configFiles as $key => $content) {
            try {
                $this->_getDomConfigModel()->merge($content);
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }
        $this->_performValidate();
        return $this->_getDomConfigModel()->getDom();
    }

    /**
     * Perform xml validation
     *
     * @param string $file
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException If invalid XML-file passed
     */
    protected function _performValidate($file = null)
    {
        $errors = [];
        $this->_getDomConfigModel()->validate($this->getSchemaFile(), $errors);
        if (!empty($errors)) {
            $phrase = (null === $file)
                ? new \Magento\Framework\Phrase('Invalid Document %1%2', [PHP_EOL, implode("\n", $errors)])
                : new \Magento\Framework\Phrase('Invalid XML-file: %1%2%3', [$file, PHP_EOL, implode("\n", $errors)]);

            throw new \Magento\Framework\Exception\LocalizedException($phrase);
        }
        return $this;
    }

    /**
     * Get Dom configuration model
     *
     * @return \Magento\Framework\Config\Dom
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _getDomConfigModel()
    {
        if (null === $this->_domConfig) {
            $this->_domConfig = $this->domFactory->createDom(
                [
                    'xml' => $this->_getInitialXml(),
                    'idAttributes' => $this->_getIdAttributes(),
                    'schemaFile' => $this->getPerFileSchemaFile()
                ]
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
