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

require_once("twig12/lib/Twig/Autoloader.php");
Twig_Autoloader::register();

class Mage_Core_Block_Template_Engine_Twig implements Mage_Core_Block_Template_EngineInterface
{
    /** @var \Magento_ObjectManager */
    protected $_objectManager;
    /** @var \Mage_Core_Model_CacheInterface */
    protected $_cache;
    /** @var \Mage_Core_Model_Dir */
    protected $_dir;

    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_CacheInterface $cache,
        Mage_Core_Model_Dir $dir )
    {
        $this->_objectManager = $objectManager;
        $this->_cache = $cache;
        $this->_dir = $dir;
    }
    /**
     * Render the named Twig template using the given block as the context of the
     * Twig helper functions/filters.  The $vars will be available to the Twig
     * template as variables.
     *
     * @param Mage_Core_Block_Template $block
     * @param string $templateFile
     * @param array $vars
     */
    public function render(Mage_Core_Block_Template $block, $fileName, $vars)
    {
        $twig = $this->getTwigEnvironment($fileName);
        $this->updateTwigGlobalsAndFilters($twig, $block);
        $data = $vars;
        $data['block'] = $block;

        echo $twig->render(basename($fileName), $data);
    }

    public function getTwigEnvironment($fileName)
    {
        $loader = new Twig_Loader_Filesystem(dirname($fileName));
        $twigEnvironmentOptions = array(
            'autoescape' => true,
            'cache' => $this->_dir->getDir(Mage_Core_Model_Dir::CACHE),
            'debug' => false,
            'optimizations' => 1,
            'strict_variables' => true,
        );
        $environment = new Twig_Environment($loader, $twigEnvironmentOptions);
        if (!$this->_cache->canUse('block_html')) {
            $environment->clearCacheFiles(); 
        }
        return $environment;
    }

    public function updateTwigGlobalsAndFilters(Twig_Environment $environment, Mage_Core_Block_Template $block)
    {
        // build the array of filters
        $filters = array();
        $filters['Mage_Core_ChildChildHtml'] = array($block, 'getChildChildHtml');
        $filters['Mage_Core_ChildHtml'] = array($block, 'getChildHtml');
        $filters['Mage_Core_ViewFileUrl'] = array($block, 'getViewFileUrl');
        $filters['translate'] = array($block, '__');

        foreach ($filters as $name => $callable) {
            //@TODO: not everything here should disable autoescape
            $options = array('is_safe' => array('html'));
            $filter = new Twig_SimpleFilter($name, $callable, $options);
            $environment->addFilter($filter);
        }

        $environment->addExtension(
            $this->_objectManager->create('Mage_Core_Block_Template_Engine_TwigExtension', array('block' => $block)));
    }

}
