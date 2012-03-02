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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test suite that excludes test groups for the disabled modules
 */
class Magento_Test_TestSuite_ModuleGroups extends PHPUnit_Framework_TestSuite
{
    /**
     * Prefix, that marks module dependent test
     */
    const PREFIX_MODULE = 'module';

    /**
     * Special character, used as separator from reserved prefixes
     */
    const PART_SEPARATOR = ':';

    /**
     * @var bool
     */
    protected $_moduleGroupsOnly;

    /**
     * @param bool $moduleGroupsOnly Whether to exclude tests with non-module group or with no group
     */
    public function __construct($moduleGroupsOnly = true)
    {
        $this->_moduleGroupsOnly = $moduleGroupsOnly;
        parent::__construct();
    }

    /**
     * Overridden to form the default values for 'group' and 'excludeGroup' arguments.
     * Forms these lists based on incoming $groups, $excludeGroups, list of enabled modules and flag of including
     * non-module groups.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  mixed                        $filter
     * @param  array                        $groups
     * @param  array                        $excludeGroups
     * @param  boolean                      $processIsolation
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE,
        array $groups = array(), array $excludeGroups = array(), $processIsolation = FALSE
    ) {
        // Keep original intent of a client, because list of groups can decrease after patterns processing
        $isIncludeGroups = !empty($groups);

        // Expand group patterns to real group names
        $groups = $this->_processPatterns($groups);
        $excludeGroups = $this->_processPatterns($excludeGroups);
        $groups = array_diff($groups, $excludeGroups);

        // Retrieve relevant and irrelevant groups
        $relevantGroups = $this->_getRelevantGroups();
        $irrelevantGroups = array_diff($this->getGroups(), $relevantGroups);

        // Modify $groups filter
        if ($isIncludeGroups) {
            $groups = array_diff($groups, $irrelevantGroups);
            if (empty($groups)) {
                // Empty groups means 'Run All', but there we want 'Run None'. So exclude tests via $excludeGroups.
                $excludeGroups = $this->getGroups();
            }
        } else {
            $groups = $relevantGroups;
        }

        // Exclude irrelevant groups. Otherwise tests, that depend both on enabled and disabled modules, will be run.
        $excludeGroups = array_merge($excludeGroups, $irrelevantGroups);
        $excludeGroups = array_unique($excludeGroups);

        return parent::run($result, $filter, $groups, $excludeGroups, $processIsolation);
    }

    /**
     * Changes group name patterns to actual group names, taken from the list of available groups.
     * E.g. '/Mage_W.*t/' will be matched and converted to 'Mage_Wishlist', 'Mage_Widget', etc.
     * Pattern is recognized by leading '/' symbol.
     *
     * @param array $groups
     * @return array
     */
    protected function _processPatterns($groups)
    {
        $result = array();
        $actualGroups = $this->getGroups();
        foreach ($groups as $groupName) {
            if (substr($groupName, 0, 1) !== '/') {
                $result[] = $groupName;
                continue;
            }

            foreach ($actualGroups as $actualGroup) {
                if (preg_match($groupName, $actualGroup)) {
                    $result[] = $actualGroup;
                }
            }
        }

        $result = array_unique($result); // Maybe regexp added several same groups
        return $result;
    }

    /**
     * Returns groups of tests for enabled modules
     *
     * @return array
     */
    protected function _getRelevantGroups()
    {
        $allGroups = $this->getGroups();
        $enabledModules = Magento_Test_Helper_Factory::getHelper('config')
            ->getEnabledModules();
        $fullPrefix = self::PREFIX_MODULE . self::PART_SEPARATOR;

        // Basic list of included modules
        $result = array();
        foreach ($enabledModules as $moduleName) {
            $result[] = $fullPrefix . $moduleName;
        }

        // Add non-module groups, if allowed
        $prefixLength = strlen($fullPrefix);
        if (!$this->_moduleGroupsOnly) {
            foreach ($allGroups as $groupName) {
                if (strncmp($groupName, $fullPrefix, $prefixLength) != 0) {
                    $result[] = $groupName;
                }
            }
        }

        return $result;
    }
}
