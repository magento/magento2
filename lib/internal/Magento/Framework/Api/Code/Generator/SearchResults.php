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
 */

class SearchResults extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'searchResults';

    /**
     * Search result default class
     */
    const SEARCH_RESULT = '\\Magento\Framework\Api\SearchResults';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
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
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [];
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName($this->_getResultClassName())
            ->setExtendedClass(self::SEARCH_RESULT)
            ->addMethods($this->_getClassMethods());
        return $this->_getGeneratedCode();
    }
}
