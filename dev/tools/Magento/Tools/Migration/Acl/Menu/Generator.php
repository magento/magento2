<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Menu;

class Generator
{
    /**
     * @var array
     */
    protected $_menuFiles;

    /**
     * @var string
     */
    protected $_basePath;

    /**
     * @var string
     */
    protected $_validNodeTypes;

    /**
     * @var array
     */
    protected $_menuIdMaps = [];

    /**
     * @var array
     */
    protected $_idToXPath = [];

    /**
     * @var array
     */
    protected $_aclXPathToId = [];

    /**
     * @var array
     */
    protected $_menuIdToAclId = [];

    /**
     * @var array
     */
    protected $_menuDomList = [];

    /**
     * @var array
     */
    protected $_updateNodes = [];

    /**
     * Is preview mode
     *
     * @var bool
     */
    protected $_isPreviewMode;

    /**
     * @var \Magento\Tools\Migration\Acl\FileManager
     */
    protected $_fileManager;

    /**
     * @param string $basePath
     * @param string $validNodeTypes
     * @param array $aclXPathToId
     * @param \Magento\Tools\Migration\Acl\FileManager $fileManager
     * @param bool $preview
     */
    public function __construct(
        $basePath,
        $validNodeTypes,
        $aclXPathToId,
        \Magento\Tools\Migration\Acl\FileManager $fileManager,
        $preview = true
    ) {
        $this->_fileManager = $fileManager;
        $this->_basePath = $basePath;
        $this->_validNodeTypes = $validNodeTypes;
        $this->_aclXPathToId = $aclXPathToId;
        $this->_updateNodes = [
            'add' => ['required' => true, 'attribute' => 'resource'],
            'update' => ['required' => false, 'attribute' => 'resource'],
        ];

        $this->_isPreviewMode = $preview;
    }

    /**
     * Get etc directory pattern
     *
     * @return null|string
     */
    public function getEtcDirPattern()
    {
        return $this->_basePath . '/app/code/*/*/*/etc/';
    }

    /**
     * @return array|null
     */
    public function getMenuFiles()
    {
        if (null === $this->_menuFiles) {
            $pattern = $this->getEtcDirPattern() . 'adminhtml/menu.xml';
            $this->_menuFiles = glob($pattern);
        }
        return $this->_menuFiles;
    }

    /**
     * Parse menu item node
     *
     * @param \DOMNode $node
     * @return void
     */
    public function parseMenuNode(\DOMNode $node)
    {
        /** @var $childNode \DOMNode **/
        foreach ($node->childNodes as $childNode) {
            if (false == in_array($childNode->nodeType, $this->_validNodeTypes) || 'add' != $childNode->nodeName) {
                continue;
            }
            $this->_menuIdMaps[$childNode->getAttribute('id')]['parent'] = $childNode->getAttribute('parent');
            $this->_menuIdMaps[$childNode->getAttribute('id')]['resource'] = $childNode->getAttribute('resource');
        }
    }

    /**
     * @return array
     */
    public function getMenuIdMaps()
    {
        return $this->_menuIdMaps;
    }

    /**
     * Parse menu files
     *
     * @return void
     */
    public function parseMenuFiles()
    {
        foreach ($this->getMenuFiles() as $file) {
            $dom = new \DOMDocument();
            $dom->load($file);
            $this->_menuDomList[$file] = $dom;
            $menus = $dom->getElementsByTagName('menu');

            /** @var $menuNode \DOMNode **/
            foreach ($menus as $menuNode) {
                $this->parseMenuNode($menuNode);
            }
        }
    }

    /**
     * @return array
     */
    public function getMenuDomList()
    {
        return $this->_menuDomList;
    }

    /**
     * @param string $menuId
     * @return void
     */
    public function initParentItems($menuId)
    {
        $this->_menuIdMaps[$menuId]['parents'] = [];
        $parentId = $this->_menuIdMaps[$menuId]['parent'];
        while ($parentId) {
            $this->_menuIdMaps[$menuId]['parents'][] = $parentId;
            if (false == isset($this->_menuIdMaps[$parentId])) {
                return;
            }
            $parentId = $this->_menuIdMaps[$parentId]['parent'];
        }
    }

    /**
     * Build xpath elements
     *
     * @param string $menuId
     * @return void
     */
    public function buildXPath($menuId)
    {
        $parents = $this->_menuIdMaps[$menuId]['parents'] ? $this->_menuIdMaps[$menuId]['parents'] : [];
        $resource = $this->_menuIdMaps[$menuId]['resource'];
        if (!$resource) {
            $parts = [];
            $parents = array_reverse($parents);
            $parents[] = $menuId;

            foreach ($parents as $parent) {
                $parentParts = explode('::', $parent);
                $idPart = $parentParts[1];
                $prevParts = implode('_', $parts);
                $start = strpos($prevParts, $idPart) + strlen($prevParts);
                $id = substr($idPart, $start);
                $parts[] = trim($id, '_');
            }
            $resource = implode('/', $parts);
        }

        $this->_idToXPath[$menuId] = $resource;
    }

    /**
     * @return array
     */
    public function getIdToXPath()
    {
        return $this->_idToXPath;
    }

    /**
     * Initialize menu items XPath
     *
     * @return void
     */
    public function buildMenuItemsXPath()
    {
        foreach (array_keys($this->_menuIdMaps) as $menuId) {
            $this->initParentItems($menuId);
            $this->buildXPath($menuId);
        }
    }

    /**
     * Map menu item id to ACL resource id
     *
     * @return array
     */
    public function mapMenuToAcl()
    {
        $output = ['mapped' => [], 'not_mapped' => []];
        $aclPrefix = 'config/acl/resources/admin/';
        foreach ($this->_idToXPath as $menuId => $menuXPath) {
            $key = $aclPrefix . $menuXPath;
            if (isset($this->_aclXPathToId[$key])) {
                $this->_menuIdToAclId[$menuId] = $this->_aclXPathToId[$key];
                $output['mapped'][] = $menuId;
            } else {
                $output['not_mapped'][] = $menuId;
            }
        }

        $output['artifacts']['MenuIdToAclId.log'] = json_encode($this->_menuIdToAclId);
        return $output;
    }

    /**
     * @return array
     */
    public function getMenuIdToAclId()
    {
        return $this->_menuIdToAclId;
    }

    /**
     * @param array $idToXPath
     * @return void
     */
    public function setIdToXPath($idToXPath)
    {
        $this->_idToXPath = $idToXPath;
    }

    /**
     * Update attributes of menu items to set ACL resource id
     *
     * @return string[]
     */
    public function updateMenuAttributes()
    {
        $errors = [];
        $aclPrefix = 'config/acl/resources/admin/';
        /** @var $dom \DOMDocument **/
        foreach ($this->_menuDomList as $file => $dom) {
            $menu = $dom->getElementsByTagName('menu')->item(0);
            /** @var $childNode \DOMNode **/
            foreach ($menu->childNodes as $childNode) {
                if (!$this->_isNodeValidToUpdate($childNode)) {
                    continue;
                }

                $attributeName = $this->_updateNodes[$childNode->nodeName]['attribute'];
                $required = $this->_updateNodes[$childNode->nodeName]['required'];
                $resource = $childNode->getAttribute($attributeName);
                $menuId = $childNode->getAttribute('id');

                if (false == array_key_exists($menuId, $this->_menuIdToAclId)) {
                    $errors[] = 'File: ' . $file . ' :: Menu: ' . $menuId . ' is not mapped to ACL id';
                    continue;
                }
                $aclId = $this->_menuIdToAclId[$menuId];

                if ($resource) {
                    $aclXPath = $aclPrefix . $resource;
                    if (false == array_key_exists($aclXPath, $this->_aclXPathToId)) {
                        $errors[] = 'File: ' .
                            $file .
                            ' :: Menu: ' .
                            $menuId .
                            '. There is no ACL resource with XPath ' .
                            $aclXPath;
                        continue;
                    }
                    $aclId = $this->_aclXPathToId[$aclXPath];
                }
                if ($required || $resource) {
                    $childNode->setAttribute($attributeName, $aclId);
                }
            }
        }

        return $errors;
    }

    /**
     * Check if node has to be updated
     *
     * @param \DOMNode $node
     * @return bool
     */
    protected function _isNodeValidToUpdate(\DOMNode $node)
    {
        if (false == in_array(
            $node->nodeType,
            $this->_validNodeTypes
        ) || false == array_key_exists(
            $node->nodeName,
            $this->_updateNodes
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param array $menuIdToAclId
     * @return void
     */
    public function setMenuIdToAclId($menuIdToAclId)
    {
        $this->_menuIdToAclId = $menuIdToAclId;
    }

    /**
     * @param array $aclXPathToId
     * @return void
     */
    public function setAclXPathToId($aclXPathToId)
    {
        $this->_aclXPathToId = $aclXPathToId;
    }

    /**
     * @param array $menuDomList
     * @return void
     */
    public function setMenuDomList($menuDomList)
    {
        $this->_menuDomList = $menuDomList;
    }

    /**
     * Save menu XML files
     *
     * @return void
     */
    public function saveMenuFiles()
    {
        if (true == $this->_isPreviewMode) {
            return;
        }
        /** @var $dom \DOMDocument **/
        foreach ($this->_menuDomList as $file => $dom) {
            $dom->formatOutput = true;
            $this->_fileManager->write($file, $dom->saveXML());
        }
    }

    /**
     * @return array
     */
    public function run()
    {
        $this->parseMenuFiles();

        $this->buildMenuItemsXPath();

        $result = $this->mapMenuToAcl();

        $result['menu_update_errors'] = $this->updateMenuAttributes();

        $this->saveMenuFiles();

        return $result;
    }
}
