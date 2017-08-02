<?php
/**
 * @category    Magento
 * @package     Magento_Code
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\EntityAbstract;

/**
 * Class Builder
 * @since 2.0.0
 */

class SearchResults extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'searchResults';

    /**
     * Search result default class
     * @deprecated
     */
    const SEARCH_RESULT = '\\' . \Magento\Framework\Api\SearchResults::class;

    /**
     * Retrieve class properties
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getClassMethods()
    {
        $getItems = [
            'name' => 'getItems',
            'parameters' => [],
            'body' => "return parent::getItems();",
            'docblock' => [
                'shortDescription' => 'Returns array of items',
                'tags' => [
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName() . '[]',
                    ],
                ],
            ],
        ];
        return [$getItems];
    }

    /**
     * Returns default constructor definition
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [];
    }

    /**
     * Generate code
     *
     * @return string
     * @since 2.0.0
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName($this->_getResultClassName())
            ->setExtendedClass('\\' . \Magento\Framework\Api\SearchResults::class)
            ->addMethods($this->_getClassMethods());
        return $this->_getGeneratedCode();
    }
}
