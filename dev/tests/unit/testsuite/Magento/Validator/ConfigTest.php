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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Validator_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Validator_Config
     */
    protected static $_model = null;

    public static function setUpBeforeClass()
    {
        self::$_model = new Magento_Validator_Config(glob(__DIR__ . '/_files/validation/positive/*/validation.xml'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        new Magento_Validator_Config(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetValidationRulesInvalidEntityName()
    {
        self::$_model->getValidationRules('invalid_entity', null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetValidationRulesInvalidGroupName()
    {
        self::$_model->getValidationRules('test_entity', 'invalid_group');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetValidationRulesInvalidZendConstraint()
    {
        $configFile = glob(__DIR__ . '/_files/validation/negative/invalid_zend_constraint.xml');
        $config = new Magento_Validator_Config($configFile);
        $config->getValidationRules('test_entity', 'test_group_a');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetValidationRulesInvalidMagentoConstraint()
    {
        $configFile = glob(__DIR__ . '/_files/validation/negative/invalid_magento_constraint.xml');
        $config = new Magento_Validator_Config($configFile);
        $config->getValidationRules('test_entity', 'test_group_a');
    }

    /**
     * @dataProvider getValidationRulesDataProvider
     * @param string $entityName
     * @param string $groupName
     * @param array $expectedRules
     */
    public function testGetValidationRules($entityName, $groupName, $expectedRules)
    {
        $actualRules = self::$_model->getValidationRules($entityName, $groupName);
        $this->assertRulesEqual($expectedRules, $actualRules);
    }

    /**
     * Assert that all expected validation rules are present with correct constraint objects.
     *
     * @param array $expectedRules
     * @param array $actualRules
     */
    public function assertRulesEqual(array $expectedRules, array $actualRules)
    {
        foreach ($expectedRules as $expectedRule => $expectedConstraints) {
            $this->assertArrayHasKey($expectedRule, $actualRules);

            foreach ($expectedConstraints as $expectedConstraint) {
                $constraintFound = false;
                foreach ($actualRules[$expectedRule] as $actualConstraint) {
                    if ($expectedConstraint['constraint'] instanceof $actualConstraint['constraint']) {
                        $constraintFound = true;
                        if (isset($expectedConstraint['field'])) {
                            $this->assertArrayHasKey('field', $actualConstraint);
                            $this->assertEquals($expectedConstraint['field'], $actualConstraint['field']);
                        }
                        break;
                    }
                }
                if (!$constraintFound) {
                    $this->fail(sprintf('Expected constraint "%s" was not found in the rule "%"',
                        get_class($expectedConstraint['constraint']), $expectedRule));
                }
            }
        }
    }

    public function getValidationRulesDataProvider()
    {
        $groupARules = array(
            'test_rule_zend' => array(
                array(
                    'constraint' => $this->getMock('Zend_Validate_Alnum'),
                    'field' => 'test_field'
                ),
            ),
            'test_rule_constraint' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                ),
            ),
        );
        $groupBRules = array(
            'test_rule_constraint' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                ),
            ),
            'test_rule_constraint_2' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                    'field' => 'constraint_field'
                ),
            ),
        );
        $groupCRules = array(
            'test_rule' => array(
                array(
                    'constraint' => $this->getMock('Zend_Validate_Int'),
                    'field' => 'test_field'
                ),
            ),
        );

        return array(
            array('test_entity', 'test_group_a', $groupARules),
            array('test_entity', 'test_group_b', $groupBRules),
            array('test_entity_b', 'test_group_c', $groupCRules),
        );
    }

    public function testGetSchemaFile()
    {
        $this->assertFileExists(self::$_model->getSchemaFile());
    }
}

/** Dummy classes to test that constraint classes extend correct abstract. */
class Magento_Validator_Invalid_Abstract
{
}
class Magento_Validator_Test extends Magento_Validator_ConstraintAbstract
{
    /**
     * @param array $data
     * @param null $field
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValidData(array $data, $field = null)
    {
        return true;
    }
}
