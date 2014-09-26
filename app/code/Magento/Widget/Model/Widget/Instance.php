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
namespace Magento\Widget\Model\Widget;

/**
 * Widget Instance Model
 *
 * @method string getTitle()
 * @method \Magento\Widget\Model\Widget\Instance setTitle(string $value)
 * @method \Magento\Widget\Model\Widget\Instance setStoreIds(string $value)
 * @method \Magento\Widget\Model\Widget\Instance setWidgetParameters(string $value)
 * @method int getSortOrder()
 * @method \Magento\Widget\Model\Widget\Instance setSortOrder(int $value)
 * @method \Magento\Widget\Model\Widget\Instance setThemeId(int $value)
 * @method int getThemeId()
 */
class Instance extends \Magento\Framework\Model\AbstractModel
{
    const SPECIFIC_ENTITIES = 'specific';

    const ALL_ENTITIES = 'all';

    const DEFAULT_LAYOUT_HANDLE = 'default';

    const PRODUCT_LAYOUT_HANDLE = 'catalog_product_view';

    const SINGLE_PRODUCT_LAYOUT_HANLDE = 'catalog_product_view_id_{{ID}}';

    const PRODUCT_TYPE_LAYOUT_HANDLE = 'catalog_product_view_type_{{TYPE}}';

    const ANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_type_layered';

    const NOTANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_type_default';

    const SINGLE_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_id_{{ID}}';

    /**
     * @var array
     */
    protected $_layoutHandles = array();

    /**
     * @var array
     */
    protected $_specificEntitiesLayoutHandles = array();

    /**
     * @var \Magento\Framework\Simplexml\Element
     */
    protected $_widgetConfigXml = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'widget_widget_instance';

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widgetModel;

    /**
     * @var \Magento\Widget\Model\NamespaceResolver
     */
    protected $_namespaceResolver;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var string[]
     */
    protected $_relatedCacheTypes;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_directory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Widget\Model\Config\Reader $reader
     * @param \Magento\Widget\Model\Widget $widgetModel
     * @param \Magento\Widget\Model\NamespaceResolver $namespaceResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param string[] $relatedCacheTypes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Widget\Model\Config\Reader $reader,
        \Magento\Widget\Model\Widget $widgetModel,
        \Magento\Widget\Model\NamespaceResolver $namespaceResolver,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $relatedCacheTypes = array(),
        array $data = array()
    ) {
        $this->_escaper = $escaper;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_relatedCacheTypes = $relatedCacheTypes;
        $this->_productType = $productType;
        $this->_reader = $reader;
        $this->_widgetModel = $widgetModel;
        $this->mathRandom = $mathRandom;
        $this->_directory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->_namespaceResolver = $namespaceResolver;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Internal Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Widget\Model\Resource\Widget\Instance');
        $this->_layoutHandles = array(
            'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::PRODUCT_LAYOUT_HANDLE,
            'all_pages' => self::DEFAULT_LAYOUT_HANDLE
        );
        $this->_specificEntitiesLayoutHandles = array(
            'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANLDE
        );
        foreach (array_keys($this->_productType->getTypes()) as $typeId) {
            $layoutHandle = str_replace('{{TYPE}}', $typeId, self::PRODUCT_TYPE_LAYOUT_HANDLE);
            $this->_layoutHandles[$typeId . '_products'] = $layoutHandle;
            $this->_specificEntitiesLayoutHandles[$typeId . '_products'] = self::SINGLE_PRODUCT_LAYOUT_HANLDE;
        }
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $pageGroupIds = array();
        $tmpPageGroups = array();
        $pageGroups = $this->getData('page_groups');
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id']) {
                        $pageGroupIds[] = $pageGroupData['page_id'];
                    }
                    if (in_array($pageGroup['page_group'], array('pages', 'page_layouts'))) {
                        $layoutHandle = $pageGroupData['layout_handle'];
                    } else {
                        $layoutHandle = $this->_layoutHandles[$pageGroup['page_group']];
                    }
                    if (!isset($pageGroupData['template'])) {
                        $pageGroupData['template'] = '';
                    }
                    $tmpPageGroup = array(
                        'page_id' => $pageGroupData['page_id'],
                        'group' => $pageGroup['page_group'],
                        'layout_handle' => $layoutHandle,
                        'for' => $pageGroupData['for'],
                        'block_reference' => $pageGroupData['block'],
                        'entities' => '',
                        'layout_handle_updates' => array($layoutHandle),
                        'template' => $pageGroupData['template'] ? $pageGroupData['template'] : ''
                    );
                    if ($pageGroupData['for'] == self::SPECIFIC_ENTITIES) {
                        $layoutHandleUpdates = array();
                        foreach (explode(',', $pageGroupData['entities']) as $entity) {
                            $layoutHandleUpdates[] = str_replace(
                                '{{ID}}',
                                $entity,
                                $this->_specificEntitiesLayoutHandles[$pageGroup['page_group']]
                            );
                        }
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                        $tmpPageGroup['layout_handle_updates'] = $layoutHandleUpdates;
                    }
                    $tmpPageGroups[] = $tmpPageGroup;
                }
            }
        }
        if (is_array($this->getData('store_ids'))) {
            $this->setData('store_ids', implode(',', $this->getData('store_ids')));
        }
        if (is_array($this->getData('widget_parameters'))) {
            $this->setData('widget_parameters', serialize($this->getData('widget_parameters')));
        }
        $this->setData('page_groups', $tmpPageGroups);
        $this->setData('page_group_ids', $pageGroupIds);

        return parent::_beforeSave();
    }

    /**
     * Validate widget instance data
     *
     * @return string|boolean
     */
    public function validate()
    {
        if ($this->isCompleteToCreate()) {
            return true;
        }
        return __('We cannot create the widget instance because it is missing required information.');
    }

    /**
     * Check if widget instance has required data (other data depends on it)
     *
     * @return boolean
     */
    public function isCompleteToCreate()
    {
        return $this->getType() && $this->getThemeId();
    }

    /**
     * Return widget instance code.  If not set, derive value from type (namespace\class).
     *
     * @return string
     */
    public function getCode()
    {
        $code = $this->_getData('instance_code');
        if ($code == null) {
            $code = $this->getWidgetReference('type', $this->getType(), 'code');
            $this->setData('instance_code', $code);
        }
        return $code;
    }

    /**
     * Sets the value this widget instance code.
     * The widget code is the 'id' attribute in the widget node.
     * 'code' is used in Magento\Widget\Model\Widget->getWidgetsArray when the array of widgets is created.
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData('instance_code', $code);
        return $this;
    }

    /**
     * Setter
     * Prepare widget type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->setData('instance_type', $type);
        return $this;
    }

    /**
     * Getter
     * Prepare widget type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getData('instance_type');
    }

    /**
     * Getter.
     * If not set return default
     *
     * @return string
     */
    public function getArea()
    {
        //TODO Shouldn't we get "area" from theme model which we can load using "theme_id"?
        if (!$this->_getData('area')) {
            return \Magento\Framework\View\DesignInterface::DEFAULT_AREA;
        }
        return $this->_getData('area');
    }

    /**
     * Getter
     * Explode to array if string setted
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (is_string($this->getData('store_ids'))) {
            return explode(',', $this->getData('store_ids'));
        }
        return $this->getData('store_ids');
    }

    /**
     * Getter
     * Unserialize if serialized string setted
     *
     * @return array
     */
    public function getWidgetParameters()
    {
        if (is_string($this->getData('widget_parameters'))) {
            return unserialize($this->getData('widget_parameters'));
        } elseif (null === $this->getData('widget_parameters')) {
            return array();
        }
        return is_array($this->getData('widget_parameters')) ? $this->getData('widget_parameters') : array();
    }

    /**
     * Retrieve option array of widget types
     *
     * @param string $value
     * @return array
     */
    public function getWidgetsOptionArray($value = 'code')
    {
        $widgets = array();
        $widgetsArr = $this->_widgetModel->getWidgetsArray();
        foreach ($widgetsArr as $widget) {
            $widgets[] = array('value' => $widget[$value], 'label' => $widget['name']);
        }
        return $widgets;
    }

    /**
     * Get the widget reference (code or namespace\class name) for the passed in type or code.
     *
     * @param string $matchParam
     * @param string $value
     * @param string $requestedParam
     * @return string|null
     */
    public function getWidgetReference($matchParam, $value, $requestedParam)
    {
        $reference = null;
        $widgetsArr = $this->_widgetModel->getWidgetsArray();
        foreach ($widgetsArr as $widget) {
            if ($widget[$matchParam] === $value) {
                $reference = $widget[$requestedParam];
                break;
            }
        }
        return $reference;
    }

    /**
     * Load widget XML config and merge with theme widget config
     *
     * @return array|null
     */
    public function getWidgetConfigAsArray()
    {
        if ($this->_widgetConfigXml === null) {
            $this->_widgetConfigXml = $this->_widgetModel->getWidgetByClassType($this->getType());
            if ($this->_widgetConfigXml) {
                $configFile = $this->_viewFileSystem->getFilename(
                    'widget.xml',
                    array(
                        'area' => $this->getArea(),
                        'theme' => $this->getThemeId(),
                        'module' => $this->_namespaceResolver->determineOmittedNamespace(
                            preg_replace('/^(.+?)\/.+$/', '\\1', $this->getType()),
                            true
                        )
                    )
                );

                $isReadable = $configFile
                    && $this->_directory->isReadable($this->_directory->getRelativePath($configFile));
                if ($isReadable) {
                    $config = $this->_reader->readFile($configFile);
                    $widgetName = isset($this->_widgetConfigXml['name']) ? $this->_widgetConfigXml['name'] : null;
                    $themeWidgetConfig = null;
                    if (!is_null($widgetName)) {
                        foreach ($config as $widget) {
                            if (isset($widget['name']) && $widgetName === $widget['name']) {
                                $themeWidgetConfig = $widget;
                                break;
                            }
                        }
                    }
                    if ($themeWidgetConfig) {
                        $this->_widgetConfigXml = array_replace_recursive($this->_widgetConfigXml, $themeWidgetConfig);
                    }
                }
            }
        }
        return $this->_widgetConfigXml;
    }

    /**
     * Retrieve widget available templates
     *
     * @return array
     */
    public function getWidgetTemplates()
    {
        $templates = array();
        $widgetConfig = $this->getWidgetConfigAsArray();
        if ($widgetConfig && isset($widgetConfig['parameters']) && isset($widgetConfig['parameters']['template'])) {
            $configTemplates = $widgetConfig['parameters']['template'];
            if (isset($configTemplates['values'])) {
                foreach ($configTemplates['values'] as $name => $template) {
                    $templates[(string)$name] = array(
                        'value' => $template['value'],
                        'label' => __((string)$template['label'])
                    );
                }
            }
        }
        return $templates;
    }

    /**
     * Get list of containers that widget is limited to be in
     *
     * @return array
     */
    public function getWidgetSupportedContainers()
    {
        $containers = array();
        $widgetConfig = $this->getWidgetConfigAsArray();
        if (isset($widgetConfig) && isset($widgetConfig['supported_containers'])) {
            $configNodes = $widgetConfig['supported_containers'];
            foreach ($configNodes as $node) {
                if (isset($node['container_name'])) {
                    $containers[] = (string)$node['container_name'];
                }
            }
        }
        return $containers;
    }

    /**
     * Retrieve widget templates that supported by specified container name
     *
     * @param string $containerName
     * @return array
     */
    public function getWidgetSupportedTemplatesByContainer($containerName)
    {
        $templates = array();
        $widgetTemplates = $this->getWidgetTemplates();
        $widgetConfig = $this->getWidgetConfigAsArray();
        if (isset($widgetConfig)) {
            if (!isset($widgetConfig['supported_containers'])) {
                return $widgetTemplates;
            }
            $configNodes = $widgetConfig['supported_containers'];
            foreach ($configNodes as $node) {
                if (isset($node['container_name']) && (string)$node['container_name'] == $containerName) {
                    if (isset($node['template'])) {
                        $templateChildren = $node['template'];
                        foreach ($templateChildren as $template) {
                            if (isset($widgetTemplates[(string)$template])) {
                                $templates[] = $widgetTemplates[(string)$template];
                            }
                        }
                    }
                }
            }
        } else {
            return $widgetTemplates;
        }
        return $templates;
    }

    /**
     * Generate layout update xml
     *
     * @param string $container
     * @param string $templatePath
     * @return string
     */
    public function generateLayoutUpdateXml($container, $templatePath = '')
    {
        $templateFilename = $this->_viewFileSystem->getTemplateFileName(
            $templatePath,
            array(
                'area' => $this->getArea(),
                'themeId' => $this->getThemeId(),
                'module' => \Magento\Framework\View\Element\AbstractBlock::extractModuleName($this->getType())
            )
        );
        if (!$this->getId() && !$this->isCompleteToCreate() || $templatePath && !is_readable($templateFilename)) {
            return '';
        }
        $parameters = $this->getWidgetParameters();
        $xml = '<referenceContainer name="' . $container . '">';
        $template = '';
        if (isset($parameters['template'])) {
            unset($parameters['template']);
        }
        if ($templatePath) {
            $template = ' template="' . $templatePath . '"';
        }

        $hash = $this->mathRandom->getUniqueHash();
        $xml .= '<block class="' . $this->getType() . '" name="' . $hash . '"' . $template . '>';
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if ($name && strlen((string)$value)) {
                $xml .= '<action method="setData">' .
                    '<argument name="name" xsi:type="string">' .
                    $name .
                    '</argument>' .
                    '<argument name="value" xsi:type="string">' .
                    $this->_escaper->escapeHtml(
                        $value
                    ) . '</argument>' . '</action>';
            }
        }
        $xml .= '</block></referenceContainer>';

        return $xml;
    }

    /**
     * Invalidate related cache types
     *
     * @return $this
     */
    protected function _invalidateCache()
    {
        if (count($this->_relatedCacheTypes)) {
            $this->_cacheTypeList->invalidate($this->_relatedCacheTypes);
        }
        return $this;
    }

    /**
     * Invalidate related cache if instance contain layout updates
     *
     * @return $this
     */
    protected function _afterSave()
    {
        if ($this->dataHasChangedFor('page_groups') || $this->dataHasChangedFor('widget_parameters')) {
            $this->_invalidateCache();
        }
        return parent::_afterSave();
    }

    /**
     * Invalidate related cache if instance contain layout updates
     *
     * @return $this
     */
    protected function _beforeDelete()
    {
        if ($this->getPageGroups()) {
            $this->_invalidateCache();
        }
        return parent::_beforeDelete();
    }
}
