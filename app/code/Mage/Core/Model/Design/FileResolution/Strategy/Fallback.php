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
 * Resolver, which performs full search of files, according to fallback rules
 */
class Mage_Core_Model_Design_FileResolution_Strategy_Fallback
    implements Mage_Core_Model_Design_FileResolution_Strategy_FileInterface,
    Mage_Core_Model_Design_FileResolution_Strategy_LocaleInterface,
    Mage_Core_Model_Design_FileResolution_Strategy_ViewInterface
{
    /**
     * @var Mage_Core_Model_Design_Fallback_Factory
     */
    protected $_fallbackFactory;

    /**
     * @var Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected $_ruleFile;

    /**
     * @var Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected $_ruleLocaleFile;

    /**
     * @var Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected $_ruleViewFile;

    /**
     * Constructor
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Design_Fallback_Factory $fallbackFactory
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Design_Fallback_Factory $fallbackFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_fallbackFactory = $fallbackFactory;
    }

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param Mage_Core_Model_Theme $themeModel
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($area, Mage_Core_Model_Theme $themeModel, $file, $module = null)
    {
        $params = array('area' => $area, 'theme' => $themeModel, 'namespace' => null, 'module' => null);
        if ($module) {
            list($params['namespace'], $params['module']) = explode('_', $module, 2);
        }
        return $this->_resolveFile($this->_getFileRule(), $file, $params);
    }

    /**
     * Get locale file name, using fallback mechanism
     *
     * @param string $area
     * @param Mage_Core_Model_Theme $themeModel
     * @param string $locale
     * @param string $file
     * @return string
     */
    public function getLocaleFile($area, Mage_Core_Model_Theme $themeModel, $locale, $file)
    {
        $params = array('area' => $area, 'theme' => $themeModel, 'locale' => $locale);
        return $this->_resolveFile($this->_getLocaleFileRule(), $file, $params);
    }

    /**
     * Get theme file name, using fallback mechanism
     *
     * @param string $area
     * @param Mage_Core_Model_Theme $themeModel
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($area, Mage_Core_Model_Theme $themeModel, $locale, $file, $module = null)
    {
        $params = array(
            'area' => $area, 'theme' => $themeModel, 'locale' => $locale, 'namespace' => null, 'module' => null
        );
        if ($module) {
            list($params['namespace'], $params['module']) = explode('_', $module, 2);
        }
        return $this->_resolveFile($this->_getViewFileRule(), $file, $params);
    }

    /**
     * Retrieve fallback rule for dynamic view files
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected function _getFileRule()
    {
        if (!$this->_ruleFile) {
            $this->_ruleFile = $this->_fallbackFactory->createFileRule();
        }
        return $this->_ruleFile;
    }

    /**
     * Retrieve fallback rule for locale files
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected function _getLocaleFileRule()
    {
        if (!$this->_ruleLocaleFile) {
            $this->_ruleLocaleFile = $this->_fallbackFactory->createLocaleFileRule();
        }
        return $this->_ruleLocaleFile;
    }

    /**
     * Retrieve fallback rule for static view files
     *
     * @return Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    protected function _getViewFileRule()
    {
        if (!$this->_ruleViewFile) {
            $this->_ruleViewFile = $this->_fallbackFactory->createViewFileRule();
        }
        return $this->_ruleViewFile;
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param Mage_Core_Model_Design_Fallback_Rule_RuleInterface $fallbackRule
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _resolveFile(
        Mage_Core_Model_Design_Fallback_Rule_RuleInterface $fallbackRule, $file, $params = array()
    ) {
        $path = '';
        foreach ($fallbackRule->getPatternDirs($params) as $dir) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, "{$dir}/{$file}");
            if ($this->_filesystem->has($path)) {
                return $path;
            }
        }
        return $path;
    }
}
