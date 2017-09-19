<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Widget Instance Model
 *
 * @api
 * @method string getTitle()
 * @method \Magento\Widget\Model\Widget\Instance setTitle(string $value)
 * @method \Magento\Widget\Model\Widget\Instance setStoreIds(string $value)
 * @method \Magento\Widget\Model\Widget\Instance setWidgetParameters(string $value)
 * @method int getSortOrder()
 * @method \Magento\Widget\Model\Widget\Instance setSortOrder(int $value)
 * @method \Magento\Widget\Model\Widget\Instance setThemeId(int $value)
 * @method int getThemeId()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
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
    protected $_layoutHandles = [];

    /**
     * @var array
     */
    protected $_specificEntitiesLayoutHandles = [];

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
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * Instance of serializer interface.
     *
     * @var Json
     */
    private $serializer;

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
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     * @param Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = [],
        Json $serializer = null
    ) {
        $this->_escaper = $escaper;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_relatedCacheTypes = $relatedCacheTypes;
        $this->_productType = $productType;
        $this->_reader = $reader;
        $this->_widgetModel = $widgetModel;
        $this->mathRandom = $mathRandom;
        $this->conditionsHelper = $conditionsHelper;
        $this->_directory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_namespaceResolver = $namespaceResolver;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
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
        $this->_init(\Magento\Widget\Model\ResourceModel\Widget\Instance::class);
        $this->_layoutHandles = [
            'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::PRODUCT_LAYOUT_HANDLE,
            'all_pages' => self::DEFAULT_LAYOUT_HANDLE,
        ];
        $this->_specificEntitiesLayoutHandles = [
            'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANLDE,
        ];
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeSave()
    {
        $pageGroupIds = [];
        $tmpPageGroups = [];
        $pageGroups = $this->getData('page_groups');
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id']) {
                        $pageGroupIds[] = $pageGroupData['page_id'];
                    }
                    if (in_array($pageGroup['page_group'], ['pages', 'page_layouts'])) {
                        $layoutHandle = $pageGroupData['layout_handle'];
                    } else {
                        $layoutHandle = $this->_layoutHandles[$pageGroup['page_group']];
                    }
                    if (!isset($pageGroupData['template'])) {
                        $pageGroupData['template'] = '';
                    }
                    $tmpPageGroup = [
                        'page_id' => $pageGroupData['page_id'],
                        'group' => $pageGroup['page_group'],
                        'layout_handle' => $layoutHandle,
                        'for' => $pageGroupData['for'],
                        'block_reference' => $pageGroupData['block'],
                        'entities' => '',
                        'layout_handle_updates' => [$layoutHandle],
                        'template' => $pageGroupData['template'] ? $pageGroupData['template'] : '',
                    ];
                    if ($pageGroupData['for'] == self::SPECIFIC_ENTITIES) {
                        $layoutHandleUpdates = [];
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

        $parameters = $this->getData('widget_parameters');
        if (is_array($parameters)) {
            if (array_key_exists('show_pager', $parameters) && !array_key_exists('page_var_name', $parameters)) {
                $parameters['page_var_name'] = 'p' . $this->mathRandom->getRandomString(
                    5,
                    \Magento\Framework\Math\Random::CHARS_LOWERS
                );
            }
            $this->setData('widget_parameters', $this->serializer->serialize($parameters));
        }
        $this->setData('page_groups', $tmpPageGroups);
        $this->setData('page_group_ids', $pageGroupIds);

        return parent::beforeSave();
    }

    /**
     * Validate widget instance data
     *
     * @return \Magento\Framework\Phrase|bool
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
            return $this->serializer->unserialize($this->getData('widget_parameters'));
        } elseif (null === $this->getData('widget_parameters')) {
            return [];
        }
        return is_array($this->getData('widget_parameters')) ? $this->getData('widget_parameters') : [];
    }

    /**
     * Retrieve option array of widget types
     *
     * @param string $value
     * @return array
     */
    public function getWidgetsOptionArray($value = 'code')
    {
        $widgets = [];
        $widgetsArr = $this->_widgetModel->getWidgetsArray();
        foreach ($widgetsArr as $widget) {
            $widgets[] = ['value' => $widget[$value], 'label' => $widget['name']];
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getWidgetConfigAsArray()
    {
        if ($this->_widgetConfigXml === null) {
            $this->_widgetConfigXml = $this->_widgetModel->getWidgetByClassType($this->getType());
            if ($this->_widgetConfigXml) {
                $configFile = $this->_viewFileSystem->getFilename(
                    'widget.xml',
                    [
                        'area' => $this->getArea(),
                        'theme' => $this->getThemeId(),
                        'module' => $this->_namespaceResolver->determineOmittedNamespace(
                            preg_replace('/^(.+?)\/.+$/', '\\1', $this->getType()),
                            true
                        )
                    ]
                );

                $isReadable = $configFile
                    && $this->_directory->isReadable($this->_directory->getRelativePath($configFile));
                if ($isReadable) {
                    $config = $this->_reader->readFile($configFile);
                    $widgetName = isset($this->_widgetConfigXml['name']) ? $this->_widgetConfigXml['name'] : null;
                    $themeWidgetConfig = null;
                    if ($widgetName !== null) {
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
        $templates = [];
        $widgetConfig = $this->getWidgetConfigAsArray();
        if ($widgetConfig && isset($widgetConfig['parameters']) && isset($widgetConfig['parameters']['template'])) {
            $configTemplates = $widgetConfig['parameters']['template'];
            if (isset($configTemplates['values'])) {
                foreach ($configTemplates['values'] as $name => $template) {
                    $templates[(string)$name] = [
                        'value' => $template['value'],
                        'label' => __((string)$template['label']),
                    ];
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
        $containers = [];
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
        $templates = [];
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function generateLayoutUpdateXml($container, $templatePath = '')
    {
        $templateFilename = $this->_viewFileSystem->getTemplateFileName(
            $templatePath,
            [
                'area' => $this->getArea(),
                'themeId' => $this->getThemeId(),
                'module' => \Magento\Framework\View\Element\AbstractBlock::extractModuleName($this->getType())
            ]
        );
        if (!$this->getId() && !$this->isCompleteToCreate() || $templatePath && !is_readable($templateFilename)) {
            return '';
        }
        $parameters = $this->getWidgetParameters();
        $xml = '<body><referenceContainer name="' . $container . '">';
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
            if ($name == 'conditions') {
                $name = 'conditions_encoded';
                $value = $this->conditionsHelper->encode($value);
            } elseif (is_array($value)) {
                $value = implode(',', $value);
            }
            if ($name && strlen((string)$value)) {
                $value = html_entity_decode($value);
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
        $xml .= '</block></referenceContainer></body>';

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
    public function afterSave()
    {
        if ($this->dataHasChangedFor('page_groups') || $this->dataHasChangedFor('widget_parameters')) {
            $this->_invalidateCache();
        }
        return parent::afterSave();
    }

    /**
     * Invalidate related cache if instance contain layout updates
     *
     * @return $this
     */
    public function beforeDelete()
    {
        if ($this->getPageGroups()) {
            $this->_invalidateCache();
        }
        return parent::beforeDelete();
    }
}
