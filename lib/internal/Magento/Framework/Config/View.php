<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * View configuration files handler
 *
 * @api
 */
class View extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @var array
     */
    protected $xpath;

    /**
     * View config data
     *
     * @var array
     */
    protected $data;

    /**
     * @var LayoutCache
     */
    private $layoutCache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $scopedLayoutCache;

    /**
     * @param FileResolverInterface $fileResolver
     * @param ConverterInterface $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @param array $xpath
     * @param LayoutCache|null $layoutCache
     * @param SerializerInterface|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName,
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global',
        $xpath = [],
        LayoutCache $layoutCache = null,
        SerializerInterface $serializer = null
    ) {
        $this->xpath = $xpath;
        $idAttributes = $this->getIdAttributes();

        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
        $this->layoutCache = $layoutCache ?? ObjectManager::getInstance()->get(LayoutCache::class);
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     */
    public function getVars($module)
    {
        $this->initData();
        return $this->data['vars'][$module] ?? [];
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false|array
     */
    public function getVarValue($module, $var)
    {
        $this->initData();
        if (!isset($this->data['vars'][$module])) {
            return false;
        }

        $value = $this->data['vars'][$module];
        foreach (explode('/', $var) as $node) {
            if (is_array($value) && isset($value[$node])) {
                $value = $value[$node];
            } else {
                return false;
            }
        }

        return $value;
    }

    /**
     * Retrieve a list media attributes in scope of specified module
     *
     * @param string $module
     * @param string $mediaType
     * @return array
     */
    public function getMediaEntities($module, $mediaType)
    {
        $this->initData();
        return $this->data['media'][$module][$mediaType] ?? [];
    }

    /**
     * Retrieve array of media attributes
     *
     * @param string $module
     * @param string $mediaType
     * @param string $mediaId
     * @return array
     */
    public function getMediaAttributes($module, $mediaType, $mediaId)
    {
        $this->initData();
        return $this->data['media'][$module][$mediaType][$mediaId] ?? [];
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function getIdAttributes()
    {
        $idAttributes = [
            '/view/vars' => 'module',
            '/view/vars/(var/)*var' => 'name',
            '/view/exclude/item' => ['type', 'item'],
        ];
        foreach ($this->xpath as $attribute) {
            if (is_array($attribute)) {
                foreach ($attribute as $key => $id) {
                    if (count($id) > 1) {
                        $idAttributes[$key] = array_values($id);
                    } else {
                        $idAttributes[$key] = array_shift($id);
                    }
                }
            }
        }
        return $idAttributes;
    }

    /**
     * Get excluded file list
     *
     * @return array
     */
    public function getExcludedFiles()
    {
        $items = $this->getItems();
        return $items['file'] ?? [];
    }

    /**
     * Get excluded directory list
     *
     * @return array
     */
    public function getExcludedDir()
    {
        $items = $this->getItems();
        return $items['directory'] ?? [];
    }

    /**
     * Get a list of excludes
     *
     * @return array
     */
    protected function getItems()
    {
        $this->initData();
        return $this->data['exclude'] ?? [];
    }

    /**
     * Initialize data array
     *
     * @return void
     */
    protected function initData()
    {
        if ($this->data === null) {
            $this->data = $this->read();
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     * @since 100.1.0
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_defaultScope;
        $layoutCacheKey = __CLASS__ . '-'. $scope . '-' . $this->_fileName;
        if (!isset($this->scopedLayoutCache[$layoutCacheKey])) {
            if ($data = $this->layoutCache->load($layoutCacheKey)) {
                $this->scopedLayoutCache[$layoutCacheKey] = $this->serializer->unserialize($data);
            } else {
                $result = [];

                $parents = (array)$this->_fileResolver->getParents($this->_fileName, $scope);
                // Sort parents desc
                krsort($parents);

                foreach ($parents as $parent) {
                    $result = array_replace_recursive($result, $this->_readFiles([$parent]));
                }

                $this->scopedLayoutCache[$layoutCacheKey] = array_replace_recursive($result, parent::read($scope));
                $this->layoutCache->save(
                    $this->serializer->serialize($this->scopedLayoutCache[$layoutCacheKey]),
                    $layoutCacheKey
                );
            }
        }
        return $this->scopedLayoutCache[$layoutCacheKey];
    }
}
