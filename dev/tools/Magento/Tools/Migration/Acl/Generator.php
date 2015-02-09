<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl;

require_once __DIR__ . '/Menu/Generator.php';
require_once __DIR__ . '/FileManager.php';
class Generator
{
    /**
     * @var bool
     */
    protected $_printHelp = false;

    /**
     * Meta node names
     *
     * @var array
     */
    protected $_metaNodeNames = [];

    /**
     * Adminhtml files
     *
     * @var array|null
     */
    protected $_adminhtmlFiles = null;

    /**
     * Parsed dom list
     *
     * @var array
     */
    protected $_parsedDomList = [];

    /**
     * Map ACL resource xpath to id
     * @var array
     */
    protected $_aclResourceMaps = [];

    /**
     * Map Menu ids
     *
     * @var array
     */
    protected $_menuIdMaps = [];

    /**
     * Base application path
     *
     * @var string|null
     */
    protected $_basePath = null;

    /**
     * Adminhtml \DOMDocument list
     *
     * @var array
     */
    protected $_adminhtmlDomList = [];

    /**
     * @var string
     */
    protected $_artifactsPath;

    /**
     * Is preview mode
     *
     * @var bool
     */
    protected $_isPreviewMode = false;

    /**
     * List of unique ACL ids
     *
     * @var array
     */
    protected $_uniqueName = [];

    /**
     * @var \Magento\Tools\Migration\Acl\Formatter
     */
    protected $_xmlFormatter;

    /**
     * @var \Magento\Tools\Migration\Acl\FileManager
     */
    protected $_fileManager;

    /**
     * @param \Magento\Tools\Migration\Acl\Formatter $xmlFormatter
     * @param \Magento\Tools\Migration\Acl\FileManager $fileManager
     * @param array $options configuration options
     */
    public function __construct(
        \Magento\Tools\Migration\Acl\Formatter $xmlFormatter,
        \Magento\Tools\Migration\Acl\FileManager $fileManager,
        $options = []
    ) {
        $this->_xmlFormatter = $xmlFormatter;
        $this->_fileManager = $fileManager;
        $this->_printHelp = array_key_exists('h', $options);
        $this->_isPreviewMode = array_key_exists('p', $options);

        $this->_metaNodeNames = ['sort_order' => 'sortOrder', 'title' => 'title'];

        $this->_basePath = realpath(__DIR__ . '/../../../../../..');

        $this->_artifactsPath = realpath(__DIR__) . '/log/';
    }

    /**
     * Get module name from file name
     *
     * @param string $fileName
     * @return string
     */
    public function getModuleName($fileName)
    {
        $parts = array_reverse(explode('/', $fileName));
        $module = $parts[3] . '_' . $parts[2];
        return $module;
    }

    /**
     * Get is forward node
     *
     * @param string $nodeName
     * @return bool
     */
    public function isForwardNode($nodeName)
    {
        return in_array($nodeName, $this->getForwardNodeNames());
    }

    /**
     * Get is meta-info node
     *
     * @param string $nodeName
     * @return bool
     */
    public function isMetaNode($nodeName)
    {
        return isset($this->_metaNodeNames[$nodeName]);
    }

    /**
     * @return string[]
     */
    public function getForwardNodeNames()
    {
        return ['children'];
    }

    /**
     * @param array $metaNodeNames
     * @return void
     */
    public function setMetaNodeNames($metaNodeNames)
    {
        $this->_metaNodeNames = $metaNodeNames;
    }

    /**
     * @return array
     */
    public function getMetaNodeNames()
    {
        return $this->_metaNodeNames;
    }

    /**
     * Get is valid node type
     *
     * @param int $nodeType
     * @return bool
     */
    public function isValidNodeType($nodeType)
    {
        return in_array($nodeType, $this->getValidNodeTypes());
    }

    /**
     * Get valid node types
     *
     * @return int[]
     */
    public function getValidNodeTypes()
    {
        return [1]; //DOMElement
    }

    /**
     * Get etc directory pattern
     *
     * @param string $codePool
     * @param string $namespace
     * @return string
     */
    public function getEtcDirPattern($codePool = '*', $namespace = '*')
    {
        return $this->getBasePath() . '/app/code/' . $codePool . '/' . $namespace . '/*/etc/';
    }

    /**
     * @param string $basePath
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->_basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Create node
     *
     * @param \DOMDocument $resultDom
     * @param string $nodeName
     * @param \DOMNode $parent
     * @return \DOMNode
     */
    public function createNode(\DOMDocument $resultDom, $nodeName, \DOMNode $parent)
    {
        $newNode = $resultDom->createElement('resource');
        $xpath = $parent->getAttribute('xpath');
        $newNode->setAttribute('xpath', $xpath . '/' . $nodeName);
        $parent->appendChild($newNode);
        $newNode->setAttribute('id', $this->generateId($newNode, $xpath, $nodeName));
        return $newNode;
    }

    /**
     * Generate unique id for ACL item
     *
     * @param \DOMNode $node
     * @param string $xpath
     * @param string $resourceId
     * @return string
     */
    public function generateId(\DOMNode $node, $xpath, $resourceId)
    {
        if (isset($this->_uniqueName[$resourceId]) && $this->_uniqueName[$resourceId] != $xpath) {
            $parts = explode('/', $node->parentNode->getAttribute('xpath'));
            $suffix = end($parts);
            $resourceId = $this->generateId($node->parentNode, $xpath, $suffix . '_' . $resourceId);
        }
        $this->_uniqueName[$resourceId] = $xpath;
        return $resourceId;
    }

    /**
     * Set meta node
     *
     * @param \DOMNode $node
     * @param \DOMNode $dataNode
     * @param string $module
     * @return void
     */
    public function setMetaInfo(\DOMNode $node, \DOMNode $dataNode, $module)
    {
        $node->setAttribute($this->_metaNodeNames[$dataNode->nodeName], $dataNode->nodeValue);
        if ($dataNode->nodeName == 'title') {
            $node->setAttribute('moduleOwner', $module);
            $resourceId = $node->getAttribute('moduleOwner') . '::' . $node->getAttribute('id');
            $xpath = $node->getAttribute('xpath');
            $this->_aclResourceMaps[$xpath] = $resourceId;
        }
    }

    /**
     * @return array
     */
    public function getAclResourceMaps()
    {
        return $this->_aclResourceMaps;
    }

    /**
     * @return array
     */
    public function getAdminhtmlFiles()
    {
        if (null === $this->_adminhtmlFiles) {
            $localFiles = glob($this->getEtcDirPattern('local') . 'adminhtml.xml');
            $communityFiles = glob($this->getEtcDirPattern('community') . 'adminhtml.xml');
            $coreFiles = glob($this->getEtcDirPattern('core') . 'adminhtml.xml');
            $this->_adminhtmlFiles = array_merge($localFiles, $communityFiles, $coreFiles);
        }
        return $this->_adminhtmlFiles;
    }

    /**
     * @param array $adminhtmlFiles
     * @return void
     */
    public function setAdminhtmlFiles($adminhtmlFiles)
    {
        $this->_adminhtmlFiles = $adminhtmlFiles;
    }

    /**
     * @return array
     */
    public function getParsedDomList()
    {
        return $this->_parsedDomList;
    }

    /**
     * Parse node
     *
     * @param \DOMNode $node - data source
     * @param \DOMDocument $dom - result \DOMDocument
     * @param \DOMNode $parentNode - parent node from result document
     * @param string $moduleName
     * @return void
     */
    public function parseNode(\DOMNode $node, \DOMDocument $dom, \DOMNode $parentNode, $moduleName)
    {
        if ($this->isRestrictedNode($node->nodeName)) {
            return;
        }

        foreach ($node->childNodes as $item) {
            if (false == $this->isValidNodeType($item->nodeType) || $this->isRestrictedNode($item->nodeName)) {
                continue;
            }

            if ($this->isForwardNode($item->nodeName)) {
                $this->parseNode($item, $dom, $parentNode, $moduleName);
            } elseif ($this->isMetaNode($item->nodeName)) {
                $this->setMetaInfo($parentNode, $item, $moduleName);
            } else {
                $newNode = $this->createNode($dom, $item->nodeName, $parentNode);
                if ($item->childNodes->length > 0) {
                    $this->parseNode($item, $dom, $newNode, $moduleName);
                }
            }
        }
    }

    /**
     * Check if node is restricted
     *
     * @param string $nodeName
     * @return bool
     */
    public function isRestrictedNode($nodeName)
    {
        return in_array($nodeName, $this->getRestrictedNodeNames());
    }

    /**
     * Print help message
     *
     * @return void
     */
    public function printHelpMessage()
    {
        $output = './acl.php -- [-hp]' . PHP_EOL;
        $output .= 'additional parameters:' . PHP_EOL;
        $output .= ' -h          print usage' . PHP_EOL;
        $output .= ' -p          preview result' . PHP_EOL;
        echo $output;
    }

    /**
     * Get template for result \DOMDocument
     *
     * @return \DOMDocument
     */
    public function getResultDomDocument()
    {
        $resultDom = new \DOMDocument();
        $resultDom->formatOutput = true;

        $config = $resultDom->createElement('config');
        $resultDom->appendChild($config);

        $acl = $resultDom->createElement('acl');
        $config->appendChild($acl);

        $parent = $resultDom->createElement('resources');
        $parent->setAttribute('xpath', 'config/acl/resources');
        $acl->appendChild($parent);
        return $resultDom;
    }

    /**
     * Parse adminhtml.xml files
     *
     * @return void
     */
    public function parseAdminhtmlFiles()
    {
        foreach ($this->getAdminhtmlFiles() as $file) {
            $module = $this->getModuleName($file);
            $resultDom = $this->getResultDomDocument();

            $adminhtmlDom = new \DOMDocument();
            $adminhtmlDom->load($file);
            $this->_adminhtmlDomList[$file] = $adminhtmlDom;

            $xpath = new \DOMXPath($adminhtmlDom);
            $resourcesList = $xpath->query('//config/acl/*');
            /** @var $aclNode \DOMNode **/
            foreach ($resourcesList as $aclNode) {
                $this->parseNode(
                    $aclNode,
                    $resultDom,
                    $resultDom->getElementsByTagName('resources')->item(0),
                    $module
                );
            }
            $this->_parsedDomList[$file] = $resultDom;
        }
    }

    /**
     * Update ACL resource id
     *
     * @return void
     */
    public function updateAclResourceIds()
    {
        /**  @var $dom \DOMDocument **/
        foreach ($this->_parsedDomList as $dom) {
            $list = $dom->getElementsByTagName('resources');
            /** @var $node \DOMNode **/
            foreach ($list as $node) {
                $node->removeAttribute('xpath');
                if ($node->childNodes->length > 0) {
                    $this->updateChildAclNodes($node);
                }
            }
        }
    }

    /**
     * @param \DOMNode $node
     * @return void
     */
    public function updateChildAclNodes($node)
    {
        /** @var $item \DOMNode **/
        foreach ($node->childNodes as $item) {
            if (false == $this->isValidNodeType($item->nodeType)) {
                continue;
            }
            $xpath = $item->getAttribute('xpath');
            $resourceId = $item->getAttribute('moduleOwner') . '::' . $item->getAttribute('id');
            if (isset($this->_aclResourceMaps[$xpath])) {
                $resourceId = $this->_aclResourceMaps[$xpath];
            }
            $item->setAttribute('id', $resourceId);
            $item->removeAttribute('xpath');
            $item->removeAttribute('moduleOwner');

            if ($item->childNodes->length > 0) {
                $this->updateChildAclNodes($item);
            }
        }
    }

    /**
     * @param array $aclResourceMaps
     * @return void
     */
    public function setAclResourceMaps($aclResourceMaps)
    {
        $this->_aclResourceMaps = $aclResourceMaps;
    }

    /**
     * Save ACL files
     *
     * @return void
     * @throws \Exception If tidy extension is not installed
     */
    public function saveAclFiles()
    {
        if ($this->_isPreviewMode) {
            return;
        }

        /** @var $dom \DOMDocument **/
        foreach ($this->_parsedDomList as $originFile => $dom) {
            $file = str_replace('adminhtml.xml', 'adminhtml' . '/acl.xml', $originFile);
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $output = $this->_xmlFormatter->parseString(
                $dom->saveXml(),
                [
                    'indent' => true,
                    'input-xml' => true,
                    'output-xml' => true,
                    'add-xml-space' => false,
                    'indent-spaces' => 4,
                    'wrap' => 1000
                ]
            );
            $this->_fileManager->write($file, $output);
        }
    }

    /**
     * @param array $parsedDomList
     * @return void
     */
    public function setParsedDomList($parsedDomList)
    {
        $this->_parsedDomList = $parsedDomList;
    }

    /**
     * @param array $adminhtmlDomList
     * @return void
     */
    public function setAdminhtmlDomList($adminhtmlDomList)
    {
        $this->_adminhtmlDomList = $adminhtmlDomList;
    }

    /**
     * @return array
     */
    public function getAdminhtmlDomList()
    {
        return $this->_adminhtmlDomList;
    }

    /**
     * Remove empty files
     *
     * @return array
     */
    public function removeAdminhtmlFiles()
    {
        $output = ['removed' => [], 'not_removed' => []];

        /** @var $dom \DOMDocument **/
        foreach ($this->_adminhtmlDomList as $file => $dom) {
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/config/acl');
            if ($nodeList->length == 0) {
                continue;
            }
            $acl = $nodeList->item(0);
            $countNodes = $acl->childNodes->length - 1;
            for ($i = $countNodes; $i >= 0; $i--) {
                $node = $acl->childNodes->item($i);
                if (in_array($node->nodeName, $this->getNodeToRemove())) {
                    $acl->removeChild($node);
                }
            }
            if ($this->isNodeEmpty($acl)) {
                if (false == $this->_isPreviewMode) {
                    $this->_fileManager->remove($file);
                }
                $output['removed'][] = $file;
            } else {
                $output['not_removed'][] = $file;
            }
        }

        $output['artifacts']['AclXPathToAclId.log'] = json_encode($this->_aclResourceMaps);
        return $output;
    }

    /**
     * Check if node is empty
     *
     * @param \DOMNode $node
     * @return bool
     */
    public function isNodeEmpty(\DOMNode $node)
    {
        $output = true;
        foreach ($node->childNodes as $item) {
            if ($this->isValidNodeType($item->nodeType)) {
                $output = false;
                break;
            }
        }
        return $output;
    }

    /**
     * @param string $artifactsPath
     * @return void
     */
    public function setArtifactsPath($artifactsPath)
    {
        $this->_artifactsPath = $artifactsPath;
    }

    /**
     * Run migration process
     *
     * @return void
     */
    public function run()
    {
        if ($this->_printHelp) {
            $this->printHelpMessage();
            return;
        }
        $this->parseAdminhtmlFiles();

        $this->updateAclResourceIds();

        $this->saveAclFiles();

        $result = $this->removeAdminhtmlFiles();

        $menuResult = $this->processMenu();

        $artifacts = array_merge($result['artifacts'], $menuResult['artifacts']);

        $this->saveArtifacts($artifacts);

        $this->printStatistic($result, $menuResult, $artifacts);
    }

    /**
     * Print statistic
     *
     * @param array $result
     * @param array $menuResult
     * @param array $artifacts
     * @return void
     */
    public function printStatistic($result, $menuResult, $artifacts)
    {
        $output = PHP_EOL;
        if (true == $this->_isPreviewMode) {
            $output .= '!!! PREVIEW MODE. ORIGIN DATA NOT CHANGED!!!' . PHP_EOL;
        }

        $output .= PHP_EOL;

        $output .= 'Removed adminhtml.xml: ' . count($result['removed']) . ' files ' . PHP_EOL;
        $output .= 'Not Removed adminhtml.xml: ' . count($result['not_removed']) . ' files ' . PHP_EOL;
        if (count($result['not_removed'])) {
            foreach ($result['not_removed'] as $fileName) {
                $output .= ' - ' . $fileName . PHP_EOL;
            }
        }

        $output .= PHP_EOL;
        $output .= 'Mapped Menu Items: ' . count($menuResult['mapped']) . PHP_EOL;
        $output .= 'Not Mapped Menu Items: ' . count($menuResult['not_mapped']) . PHP_EOL;

        if (count($menuResult['not_mapped'])) {
            foreach ($menuResult['not_mapped'] as $menuId) {
                $output .= ' - ' . $menuId . PHP_EOL;
            }
        }

        $output .= 'Menu Update Errors: ' . count($menuResult['menu_update_errors']) . PHP_EOL;
        if (count($menuResult['menu_update_errors'])) {
            foreach ($menuResult['menu_update_errors'] as $errorText) {
                $output .= ' - ' . $errorText . PHP_EOL;
            }
        }

        $output .= PHP_EOL;
        $output .= 'Artifacts: ' . PHP_EOL;
        foreach (array_keys($artifacts) as $file) {
            $output .= ' - ' . $this->_artifactsPath . $file . PHP_EOL;
        }

        echo $output;
    }

    /**
     * Save artifacts files
     *
     * @param array $artifacts
     * @return void
     */
    public function saveArtifacts($artifacts)
    {
        foreach ($artifacts as $file => $data) {
            $this->_fileManager->write($this->_artifactsPath . $file, $data);
        }
    }

    /**
     * Run process of menu updating
     *
     * @return array
     */
    public function processMenu()
    {
        $menu = new \Magento\Tools\Migration\Acl\Menu\Generator(
            $this->getBasePath(),
            $this->getValidNodeTypes(),
            $this->_aclResourceMaps,
            $this->_fileManager,
            $this->_isPreviewMode
        );
        return $menu->run();
    }

    /**
     * @return array
     */
    public function getRestrictedNodeNames()
    {
        return ['privilegeSets'];
    }

    /**
     * @return array
     */
    public function getNodeToRemove()
    {
        return ['resources', 'privilegeSets'];
    }
}
