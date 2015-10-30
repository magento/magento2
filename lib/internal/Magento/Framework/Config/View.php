<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * View configuration files handler
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Dom\UrnResolver;

class View extends \Magento\Framework\Config\Reader\Filesystem
{
    /** @var UrnResolver */
    protected $urnResolver;

    /**
     * @var array
     */
    protected $xpath;

    /*
     * @var array
     */
    private $data;

    /**
     * @param FileResolverInterface $fileResolver
     * @param ValidationStateInterface $validationState
     * @param UrnResolver $urnResolver
     * @param ValidationStateInterface $fileName
     * @param ConverterInterface $converterInterface
     * @param SchemaLocatorInterface $schemaLocatorInterface
     * @param array $xpath
     */
    public function __construct(
        ValidationStateInterface $validationState,
        UrnResolver $urnResolver,
        ConverterInterface $converterInterface,
        SchemaLocatorInterface $schemaLocatorInterface,
        FileResolverInterface $fileResolver,
        $fileName,
        $xpath = []
    ) {
        $this->xpath = $xpath;
        $this->urnResolver = $urnResolver;
        $idAttributes = $this->_getIdAttributes();
        parent::__construct(
            $fileResolver,
            $converterInterface,
            $schemaLocatorInterface,
            $validationState,
            $fileName,
            $idAttributes
        );
        $this->data = $this->read();
    }

    /**
     * Path to view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/view.xsd');
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
        return isset($this->data['vars'][$module]) ? $this->data['vars'][$module] : [];
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
        return isset($this->data['media'][$module][$mediaType]) ? $this->data['media'][$module][$mediaType] : [];
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
        return isset($this->data['media'][$module][$mediaType][$mediaId])
            ? $this->data['media'][$module][$mediaType][$mediaId]
            : [];
    }

    /**
     * Return copy of DOM
     *
     * @return \Magento\Framework\Config\Dom
     */
    public function getDomConfigCopy()
    {
        return clone $this->_getDomConfigModel();
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        $idAttributes = $this->addIdAttributes($this->xpath);
        return $idAttributes;
    }

    /**
     * Add attributes for module identification
     *
     * @param array $xpath
     * @return array
     */
    protected function addIdAttributes($xpath)
    {
        $idAttributes = [
            '/view/vars' => 'module',
            '/view/vars/var' => 'name',
            '/view/exclude/item' => ['type', 'item'],
        ];
        foreach ($xpath as $attribute) {
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
        return isset($items['file']) ? $items['file'] : [];
    }

    /**
     * Get excluded directory list
     *
     * @return array
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
     */
    protected function getItems()
    {
        return isset($this->data['exclude']) ? $this->data['exclude'] : [];
    }
}
