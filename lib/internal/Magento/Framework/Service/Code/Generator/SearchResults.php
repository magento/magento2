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
 * @category    Magento
 * @package     Magento_Code
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Service\Code\Generator;

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
    const SEARCH_RESULT = '\\Magento\Framework\Service\V1\Data\SearchResults';

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
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . '[]',
                    ]
                ]
            ]
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
