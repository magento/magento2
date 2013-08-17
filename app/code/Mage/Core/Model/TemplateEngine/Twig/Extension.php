<?php
/**
 * A twig extension for Magento
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_TemplateEngine_Twig_Extension
    extends Twig_Extension
{
    const MAGENTO = 'Magento';

    /**
     * @var Mage_Core_Model_TemplateEngine_Twig_LayoutFunctions
     */
    protected $_layoutFunctions;

    /**
     * @var Mage_Core_Model_TemplateEngine_Twig_CommonFunctions
     */
    protected $_commonFunctions;

    /**
     * @var Mage_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Mage_Core_Model_TemplateEngine_BlockTrackerInterface
     */
    private $_blockTracker;

    /**
     * Create new Extension
     *
     * @param Mage_Core_Model_TemplateEngine_Twig_CommonFunctions $commonFunctions
     * @param Mage_Core_Model_TemplateEngine_Twig_LayoutFunctions $layoutFunctions
     * @param Mage_Core_Model_Translate $translate
     */
    public function __construct(
        Mage_Core_Model_TemplateEngine_Twig_CommonFunctions $commonFunctions,
        Mage_Core_Model_TemplateEngine_Twig_LayoutFunctions $layoutFunctions,
        Mage_Core_Model_Translate $translate
    ) {
        $this->_commonFunctions = $commonFunctions;
        $this->_layoutFunctions = $layoutFunctions;
        $this->_translator = $translate;
    }

    /**
     * Define the name of the extension to be used in Twig environment
     *
     * @return string
     */
    public function getName()
    {
        return self::MAGENTO;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $functions = $this->_commonFunctions->getFunctions();
        $functions = array_merge($functions, $this->_layoutFunctions->getFunctions());

        return $functions;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new Twig_SimpleFilter('translate', array($this, 'translate'), $options),
        );
    }

    /**
     * Translate block sentence
     *
     * @return string
     */
    public function translate()
    {
        $currentModuleName =  Mage_Core_Block_Abstract::extractModuleName(
            get_class($this->_blockTracker->getCurrentBlock())
        );
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $currentModuleName);
        array_unshift($args, $expr);
        return $this->_translator->translate($args);
    }

    /**
     * Sets the block tracker
     *
     * @param Mage_Core_Model_TemplateEngine_BlockTrackerInterface $blockTracker
     */
    public function setBlockTracker(Mage_Core_Model_TemplateEngine_BlockTrackerInterface $blockTracker)
    {
        $this->_blockTracker = $blockTracker;
        // Need to inject this dependency at runtime to avoid cyclical dependency
        $this->_layoutFunctions->setBlockTracker($blockTracker);
    }

}