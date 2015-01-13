<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration;

/**
 * System configuration migration reader
 */
class Reader
{
    /**
     * @var \Magento\Tools\Migration\System\FileManager
     */
    protected $_fileManager;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Parser
     */
    protected $_parser;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper
     */
    protected $_mapper;

    /**
     * @var string base application path
     */
    protected $_basePath;

    /**
     * pattern to find all system.xml files
     */
    const SYSTEM_CONFIG_PATH_PATTERN = 'app/code/*/*/*/etc/system.xml';

    /**
     * @param \Magento\Tools\Migration\System\FileManager $fileManager
     * @param \Magento\Tools\Migration\System\Configuration\Parser $parser
     * @param \Magento\Tools\Migration\System\Configuration\Mapper $mapper Tools_Migration_System_Configuration_Mapper
     */
    public function __construct(
        \Magento\Tools\Migration\System\FileManager $fileManager,
        \Magento\Tools\Migration\System\Configuration\Parser $parser,
        \Magento\Tools\Migration\System\Configuration\Mapper $mapper
    ) {
        $this->_fileManager = $fileManager;
        $this->_parser = $parser;
        $this->_mapper = $mapper;

        $this->_basePath = realpath(__DIR__ . '/../../../../../../..');
    }

    /**
     * Get configuration per file
     *
     * @return array
     */
    public function getConfiguration()
    {
        $files = $this->_fileManager->getFileList(
            $this->_basePath . '/' . \Magento\Tools\Migration\System\Configuration\Reader::SYSTEM_CONFIG_PATH_PATTERN
        );
        $result = [];
        foreach ($files as $fileName) {
            $result[$fileName] = $this->_mapper->transform(
                $this->_parser->parse($this->_getDOMDocument($this->_fileManager->getContents($fileName)))
            );
        }

        return $result;
    }

    /**
     * Create Dom document from xml string
     *
     * @param string $xml
     * @return \DOMDocument
     */
    protected function _getDOMDocument($xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        return $dom;
    }
}
