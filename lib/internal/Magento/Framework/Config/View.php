<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * View configuration files handler
 *
 * @api
 * @since 2.0.0
 */
class View extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $xpath;

    /**
     * View config data
     *
     * @var array
     * @since 2.0.0
     */
    protected $data;

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
     * @since 2.0.0
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
        $xpath = []
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
    }

    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     * @since 2.0.0
     */
    public function getVars($module)
    {
        $this->initData();
        return isset($this->data['vars'][$module]) ? $this->data['vars'][$module] : [];
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false|array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMediaEntities($module, $mediaType)
    {
        $this->initData();
        return isset($this->data['media'][$module][$mediaType]) ? $this->data['media'][$module][$mediaType] : [];
    }

    /**
     * Retrieve array of media attributes
     *
     * @param string $module
     * @param string $mediaType
     * @param string $mediaId
     * @return array
     * @since 2.0.0
     */
    public function getMediaAttributes($module, $mediaType, $mediaId)
    {
        $this->initData();
        return isset($this->data['media'][$module][$mediaType][$mediaId])
            ? $this->data['media'][$module][$mediaType][$mediaId]
            : [];
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getExcludedFiles()
    {
        $items = $this->getItems();
        return isset($items['file']) ? $items['file'] : [];
    }

    /**
     * Get excluded directory list
     *
     * @return array
     * @since 2.0.0
     */
    public function getExcludedDir()
    {
        $items = $this->getItems();
        return isset($items['directory']) ? $items['directory'] : [];
    }

    /**
     * Get a list of excludes
     *
     * @return array
     * @since 2.0.0
     */
    protected function getItems()
    {
        $this->initData();
        return isset($this->data['exclude']) ? $this->data['exclude'] : [];
    }

    /**
     * Initialize data array
     *
     * @return void
     * @since 2.0.0
     */
    protected function initData()
    {
        if ($this->data === null) {
            $this->data = $this->read();
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_defaultScope;
        $result = [];

        $parents = (array)$this->_fileResolver->getParents($this->_fileName, $scope);
        // Sort parents desc
        krsort($parents);

        foreach ($parents as $parent) {
            $result = array_replace_recursive($result, $this->_readFiles([$parent]));
        }

        return array_replace_recursive($result, parent::read($scope));
    }
}
