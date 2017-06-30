<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * System configuration structure.
 *
 * All paths are declared in module's system.xml.
 *
 * ```xml
 * <section id="section_id">
 *      <group id="group_id" ...>
 *          <field id="field_one_id" ...>
 *              <label>Field One</label>
 *              ...
 *          </field>
 *          <field id="field_two_id" ...>
 *              <label>Field Two</label>
 *              <config_path>section/group/field</config_path>
 *              ...
 *          </field>
 *      </group>
 * </section>
 * ```
 *
 * Structure path is the nested path of node ids (section, group, field).
 *
 * Config path is the path which is declared in <config_path> node.
 * If this node is not provided then config path is the same as structure path.
 *
 * With the example above you can see that the field <field id="field_one_id"> has the next paths:
 *  - the structure path section_id/group_id/field_one_id
 *  - the configuration path section_id/group_id/field_one_id
 *
 * Also you can see that the field <field id="field_two_id"> has the next paths:
 * - the structure path section_id/group_id/field_two_id
 * - the configuration path section/group/field
 *
 * @api
 */
class Structure implements \Magento\Config\Model\Config\Structure\SearchInterface
{
    /**
     * Key that contains field type in structure array
     */
    const TYPE_KEY = '_elementType';

    /**
     * Configuration structure represented as tree
     *
     * @var array
     */
    protected $_data;

    /**
     * Config tab iterator
     *
     * @var \Magento\Config\Model\Config\Structure\Element\Iterator\Tab
     */
    protected $_tabIterator;

    /**
     * Pool of config element flyweight objects
     *
     * @var \Magento\Config\Model\Config\Structure\Element\FlyweightFactory
     */
    protected $_flyweightFactory;

    /**
     * Provider of current config scope
     *
     * @var ScopeDefiner
     */
    protected $_scopeDefiner;

    /**
     * List of cached elements
     *
     * @var \Magento\Config\Model\Config\Structure\ElementInterface[]
     */
    protected $_elements;

    /**
     * List of config sections
     *
     * @var array
     */
    protected $sectionList;

    /**
     * Collects config paths and their structure paths from configuration files
     *
     * For example:
     * ```php
     * [
     *  'section_id/group_id/field_one_id' => [
     *      'section_id/group_id/field_one_id'
     *  ],
     * 'section/group/field' => [
     *      'section_id/group_id/field_two_id'
     * ]
     * ```
     *
     * @var array
     */
    private $mappedPaths;

    /**
     * @param \Magento\Config\Model\Config\Structure\Data $structureData
     * @param \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator
     * @param \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory
     * @param ScopeDefiner $scopeDefiner
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Data $structureData,
        \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator,
        \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory,
        ScopeDefiner $scopeDefiner
    ) {
        $this->_data = $structureData->get();
        $this->_tabIterator = $tabIterator;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
    }

    /**
     * Retrieve tab iterator
     *
     * @return \Magento\Config\Model\Config\Structure\Element\Iterator
     */
    public function getTabs()
    {
        if (isset($this->_data['sections'])) {
            foreach ($this->_data['sections'] as $sectionId => $section) {
                if (isset($section['tab']) && $section['tab']) {
                    $this->_data['tabs'][$section['tab']]['children'][$sectionId] = $section;
                }
            }
            $this->_tabIterator->setElements($this->_data['tabs'], $this->_scopeDefiner->getScope());
        }
        return $this->_tabIterator;
    }

    /**
     * Retrieve config section list
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getSectionList()
    {
        if (empty($this->sectionList)) {
            foreach ($this->_data['sections'] as $sectionId => $section) {
                if (array_key_exists('children', $section) && is_array($section['children'])) {
                    foreach ($section['children'] as $childId => $child) {
                        $this->sectionList[$sectionId . '_' . $childId] = true;
                    }
                }
            }
        }
        return $this->sectionList;
    }

    /**
     * Find element by structure path
     *
     * @param string $path The structure path
     * @return \Magento\Config\Model\Config\Structure\ElementInterface|null
     */
    public function getElement($path)
    {
        return $this->getElementByPathParts(explode('/', $path));
    }

    /**
     * Find element by config path
     *
     * @param string $path The configuration path
     * @return \Magento\Config\Model\Config\Structure\ElementInterface|null
     */
    public function getElementByConfigPath($path)
    {
        $allPaths = $this->getFieldPaths();

        if (isset($allPaths[$path])) {
            $path = array_shift($allPaths[$path]);
        }

        return $this->getElementByPathParts(explode('/', $path));
    }

    /**
     * Retrieve first available section in config structure
     *
     * @return Structure\ElementInterface
     * @throws LocalizedException
     */
    public function getFirstSection()
    {
        $tabs = $this->getTabs();
        $tabs->rewind();
        /** @var $tab \Magento\Config\Model\Config\Structure\Element\Tab */
        $tab = $tabs->current();
        $tab->getChildren()->rewind();
        if (!$tab->getChildren()->current()->isVisible()) {
            throw new LocalizedException(__('Visible section not found.'));
        }

        return $tab->getChildren()->current();
    }

    /**
     * Find element by path parts
     *
     * @param string[] $pathParts
     * @return \Magento\Config\Model\Config\Structure\ElementInterface|null
     */
    public function getElementByPathParts(array $pathParts)
    {
        $path = implode('_', $pathParts);
        if (isset($this->_elements[$path])) {
            return $this->_elements[$path];
        }
        $children = [];
        if ($this->_data) {
            $children = $this->_data['sections'];
        }
        $child = [];
        foreach ($pathParts as $pathPart) {
            if ($children && (array_key_exists($pathPart, $children))) {
                $child = $children[$pathPart];
                $children = array_key_exists('children', $child) ? $child['children'] : [];
            } else {
                $child = $this->_createEmptyElement($pathParts);
                break;
            }
        }
        $this->_elements[$path] = $this->_flyweightFactory->create($child['_elementType']);
        $this->_elements[$path]->setData($child, $this->_scopeDefiner->getScope());
        return $this->_elements[$path];
    }

    /**
     * Create empty element data
     *
     * @param string[] $pathParts
     * @return array
     */
    protected function _createEmptyElement(array $pathParts)
    {
        switch (count($pathParts)) {
            case 1:
                $elementType = 'section';
                break;
            case 2:
                $elementType = 'group';
                break;
            default:
                $elementType = 'field';
        }
        $elementId = array_pop($pathParts);
        return ['id' => $elementId, 'path' => implode('/', $pathParts), '_elementType' => $elementType];
    }

    /**
     * Retrieve paths of fields that have provided attributes with provided values
     *
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return array
     */
    public function getFieldPathsByAttribute($attributeName, $attributeValue)
    {
        $result = [];
        foreach ($this->_data['sections'] as $section) {
            if (!isset($section['children'])) {
                continue;
            }
            foreach ($section['children'] as $group) {
                if (isset($group['children'])) {
                    $path = $section['id'] . '/' . $group['id'];
                    $result = array_merge(
                        $result,
                        $this->_getGroupFieldPathsByAttribute(
                            $group['children'],
                            $path,
                            $attributeName,
                            $attributeValue
                        )
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Find group fields with specified attribute and attribute value
     *
     * @param array $fields
     * @param string $parentPath
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return array
     */
    protected function _getGroupFieldPathsByAttribute(array $fields, $parentPath, $attributeName, $attributeValue)
    {
        $result = [];
        foreach ($fields as $field) {
            if (isset($field['children'])) {
                $result += $this->_getGroupFieldPathsByAttribute(
                    $field['children'],
                    $parentPath . '/' . $field['id'],
                    $attributeName,
                    $attributeValue
                );
            } elseif (isset($field[$attributeName]) && $field[$attributeName] == $attributeValue) {
                $result[] = $parentPath . '/' . $field['id'];
            }
        }
        return $result;
    }

    /**
     * Collects config paths and their structure paths from configuration files.
     * Returns the map of config paths and their structure paths.
     *
     * All paths are declared in module's system.xml.
     *
     * ```xml
     * <section id="section_id">
     *      <group id="group_id" ...>
     *          <field id="field_one_id" ...>
     *              <label>Field One</label>
     *              ...
     *          </field>
     *          <field id="field_two_id" ...>
     *              <label>Field Two</label>
     *              <config_path>section/group/field</config_path>
     *              ...
     *          </field>
     *      </group>
     * </section>
     * ```
     * If <config_path> node does not exist, then config path duplicates structure path.
     * The result of this example will be:
     *
     * ```php
     * [
     *  'section_id/group_id/field_one_id' => [
     *      'section_id/group_id/field_one_id'
     *  ],
     * 'section/group/field' => [
     *      'section_id/group_id/field_two_id'
     * ]
     *```
     *
     * @return array An array of config path to config structure path map
     */
    public function getFieldPaths()
    {
        $sections = !empty($this->_data['sections']) ? $this->_data['sections'] : [];

        if (!$this->mappedPaths) {
            $this->mappedPaths = $this->getFieldsRecursively($sections);
        }

        return $this->mappedPaths;
    }

    /**
     * Iteration that collects config field paths recursively from config files.
     *
     * @param array $elements The elements to be parsed
     * @return array An array of config path to config structure path map
     */
    private function getFieldsRecursively(array $elements = [])
    {
        $result = [];

        foreach ($elements as $element) {
            if (isset($element['children'])) {
                $result = array_replace_recursive(
                    $result,
                    $this->getFieldsRecursively($element['children'])
                );
            } else {
                if ($element['_elementType'] === 'field' && isset($element['label'])) {
                    $structurePath = (isset($element['path']) ? $element['path'] . '/' : '') . $element['id'];
                    $configPath = isset($element['config_path']) ? $element['config_path'] : $structurePath;

                    if (!isset($result[$configPath])) {
                        $result[$configPath] = [];
                    }

                    $result[$configPath][] = $structurePath;
                }
            }
        }

        return $result;
    }
}
