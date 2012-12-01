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
 * @package     Magento_Validator
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Validation configuration files handler
 */
class Magento_Validator_Config extends Magento_Config_XmlAbstract
{
    /**
     * Get absolute path to validation.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/validation.xsd';
    }

    /**
     * Get validation rules for specified entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @throws InvalidArgumentException
     * @return array
     */
    public function getValidationRules($entityName, $groupName)
    {
        if (!isset($this->_data[$entityName])) {
            throw new InvalidArgumentException(sprintf('Unknown validation entity "%s"', $entityName));
        }
        if (!isset($this->_data[$entityName]['groups'][$groupName])) {
            throw new InvalidArgumentException(sprintf('Unknown validation group "%s" in entity "%s"', $groupName,
                $entityName));
        }

        $result = array();
        $groupRules = $this->_data[$entityName]['groups'][$groupName];
        foreach ($groupRules as $ruleName) {
            $rule = $this->_data[$entityName]['rules'][$ruleName];
            foreach ($rule['constraints'] as $constraintConfig) {
                $className = $constraintConfig['class'];
                $constraint = new $className();
                if (!($constraint instanceof Zend_Validate_Interface
                    || $constraint instanceof Magento_Validator_ConstraintAbstract)) {
                    throw new InvalidArgumentException(sprintf('Constraint "%s" must implement either '
                        . 'Zend_Validate_Interface or Magento_Validator_ConstraintAbstract', $className));
                }
                if ($constraint instanceof Zend_Validate_Interface && empty($constraintConfig['field'])) {
                    throw new InvalidArgumentException(sprintf('Constraint "%s" must have "field" attribute defined.',
                        $className));
                }
                $result[$ruleName][] = array(
                    'constraint' => $constraint,
                    'field' => $constraintConfig['field'],
                );
            }
        }

        return $result;
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @throws Magento_Exception
     * @return array
     */
    protected function _extractData(DOMDocument $dom)
    {
        $result = array();
        /** @var DOMElement $entity */
        foreach ($dom->getElementsByTagName('entity') as $entity) {
            $entityName = $entity->getAttribute('name');
            $result[$entityName]['rules'] = array();
            /** @var DOMElement $rule */
            foreach ($entity->getElementsByTagName('rule') as $rule) {
                $ruleName = $rule->getAttribute('name');
                $result[$entityName]['rules'][$ruleName] = array();
                /** @var DOMElement $constraint */
                foreach ($rule->getElementsByTagName('constraint') as $constraint) {
                    $result[$entityName]['rules'][$ruleName]['constraints'][] = array(
                        'class' => $constraint->getAttribute('class'),
                        'field' => $constraint->getAttribute('field'),
                    );
                }
            }

            $result[$entityName]['groups'] = array();
            /** @var DOMElement $group */
            foreach ($entity->getElementsByTagName('group') as $group) {
                $groupName = $group->getAttribute('name');
                $result[$entityName]['groups'][$groupName] = array();
                /** @var DOMElement $use */
                foreach ($group->getElementsByTagName('use') as $use) {
                    $usesRuleName = $use->getAttribute('rule');
                    $result[$entityName]['groups'][$groupName][] = $usesRuleName;
                }
            }
        }

        return $result;
    }

    /**
     * Get initial XML of a valid document
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><validation></validation>';
    }

    /**
     * Define id attributes for entities
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array(
            '/validation/entity' => 'name',
            '/validation/entity/rules/rule' => 'name',
            '/validation/entity/rules/rule/constraints/constraint' => 'class',
            '/validation/entity/groups/group' => 'name',
            '/validation/entity/groups/group/uses/use' => 'rule',
        );
    }
}
