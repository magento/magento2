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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class with substitution parameters to values considering theme hierarchy
 */
class Mage_Core_Model_Design_Fallback_Rule_Theme implements Mage_Core_Model_Design_Fallback_Rule_RuleInterface
{
    /**
     * @var Mage_Core_Model_Design_Fallback_Rule_RuleInterface[]
     */
    protected $_rules;

    /**
     * Constructor
     *
     * @param array $rules Rules to be propagated to every theme involved into inheritance
     * @throws InvalidArgumentException
     */
    public function __construct(array $rules)
    {
        foreach ($rules as $rule) {
            if (!($rule instanceof Mage_Core_Model_Design_Fallback_Rule_RuleInterface)) {
                throw new InvalidArgumentException(
                    'Each element should implement Mage_Core_Model_Design_Fallback_Rule_RuleInterface'
                );
            }
        }
        $this->_rules = $rules;
    }

    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params - array of parameters
     * @return array of folders to perform a search
     * @throws InvalidArgumentException
     */
    public function getPatternDirs(array $params)
    {
        if (!array_key_exists('theme', $params) || !($params['theme'] instanceof Mage_Core_Model_ThemeInterface)) {
            throw new InvalidArgumentException(
                '$params["theme"] should be passed and should implement Mage_Core_Model_ThemeInterface'
            );
        }
        $result = array();
        /** @var $theme Mage_Core_Model_ThemeInterface */
        $theme = $params['theme'];
        while ($theme) {
            if ($theme->getThemePath()) {
                $params['theme_path'] = $theme->getThemePath();
                foreach ($this->_rules as $rule) {
                    $result = array_merge($result, $rule->getPatternDirs($params));
                }
            }
            $theme = $theme->getParentTheme();
        }
        return $result;
    }
}
