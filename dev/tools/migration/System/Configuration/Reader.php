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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System configuration migration reader
 */
class Tools_Migration_System_Configuration_Reader
{
    /**
     * @var Tools_Migration_System_FileManager
     */
    protected $_fileManager;

    /**
     * @var Tools_Migration_System_Configuration_Parser
     */
    protected $_parser;

    /**
     * @var Tools_Migration_System_Configuration_Mapper
     */
    protected $_mapper;

    /**
     * @var string base application path
     */
    protected $_basePath;

    /**
     * pattern to find all system.xml files
     */
    CONST SYSTEM_CONFIG_PATH_PATTERN = 'app/code/*/*/*/etc/system.xml';

    /**
     * @param Tools_Migration_System_FileManager $fileManager
     * @param Tools_Migration_System_Configuration_Parser $parser
     * @param Tools_Migration_System_Configuration_Mapper $mapper
     */
    public function __construct(
        Tools_Migration_System_FileManager $fileManager,
        Tools_Migration_System_Configuration_Parser $parser,
        Tools_Migration_System_Configuration_Mapper $mapper
    ) {
        $this->_fileManager = $fileManager;
        $this->_parser = $parser;
        $this->_mapper = $mapper;

        $this->_basePath = realpath(dirname(__FILE__) . '/../../../../..');
    }

    /**
     * Get configuration per file
     *
     * @return array
     */
    public function getConfiguration()
    {
        $files = $this->_fileManager->getFileList(
            $this->_basePath . DIRECTORY_SEPARATOR
            . Tools_Migration_System_Configuration_Reader::SYSTEM_CONFIG_PATH_PATTERN
        );
        $result = array();
        foreach ($files as $fileName) {
            $result[$fileName] = $this->_mapper->transform(
                $this->_parser->parse(
                    $this->_getDOMDocument(
                        $this->_fileManager->getContents($fileName)
                    )
                )
            );
        }

        return $result;
    }

    /**
     * Create Dom document from xml string
     *
     * @param $xml
     * @return DOMDocument
     */
    protected function _getDOMDocument($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom;
    }
}
