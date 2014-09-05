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
class SearchResultsBuilder extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'searchResultsBuilder';

    /**
     * Search result builder abstract class
     */
    const SEARCH_RESULT_BUILDER = '\\Magento\Framework\Service\V1\Data\AbstractSearchResultsBuilder';

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
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                [
                    'name' => 'objectFactory',
                    'type' => '\\Magento\Framework\Service\Data\ObjectFactory'
                ],
                [
                    'name' => 'valueBuilder',
                    'type' => '\\Magento\Framework\Service\Data\AttributeValueBuilder'
                ],
                [
                    'name' => 'metadataService',
                    'type' => '\\Magento\Framework\Service\Config\MetadataConfig'
                ],
                [
                    'name' => 'searchCriteriaBuilder',
                    'type' => '\\Magento\Framework\Service\V1\Data\SearchCriteriaBuilder'
                ],
                [
                    'name' => 'itemObjectBuilder',
                    'type' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Builder'
                ],
            ],
            'body' => "parent::__construct(\$objectFactory, \$valueBuilder, \$metadataService, " .
                "\$searchCriteriaBuilder, \$itemObjectBuilder);",
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => ''
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        return [$this->_getDefaultConstructorDefinition()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->_getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . 'SearchResultsBuilder') {
                $this->_addError(
                    'Invalid Result class name [' . $resultClassName . ']. Use '
                    . $sourceClassName . 'SearchResultsBuilder'
                );
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName(
            $this->_getResultClassName()
        )->addMethods(
            $this->_getClassMethods()
        )->setClassDocBlock(
            $this->_getClassDocBlock()
        )->setExtendedClass(self::SEARCH_RESULT_BUILDER);
        return $this->_getGeneratedCode();
    }
}
