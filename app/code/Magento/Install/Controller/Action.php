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
 * @package     Magento_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Install\Controller;

class Action extends \Magento\Core\Controller\Varien\Action
{
    /**
     * @var \Magento\Config\Scope
     */
    protected $_configScope;

    /**
     * @var \Magento\View\DesignInterface
     */
    protected $_viewDesign;

    /**
     * @var \Magento\Core\Model\Theme\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Application
     *
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * Application state
     *
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Config\Scope $configScope,
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Theme\CollectionFactory $collectionFactory,
        \Magento\Core\Model\App $app,
        \Magento\App\State $appState
    ) {
        $this->_configScope = $configScope;
        $this->_viewDesign = $viewDesign;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->_app = $app;
        $this->_appState = $appState;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_configScope->setCurrentScope('install');
        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, true);
    }

    /**
     * Initialize area and design
     *
     * @return \Magento\Install\Controller\Action
     */
    protected function _initDesign()
    {
        $areaCode = $this->getLayout()->getArea();
        $area = $this->_app->getArea($areaCode);
        $area->load(\Magento\Core\Model\App\Area::PART_CONFIG);
        $this->_initDefaultTheme($areaCode);
        $area->detectDesign($this->getRequest());
        $area->load(\Magento\Core\Model\App\Area::PART_TRANSLATE);
        return $this;
    }

    /**
     * Initialize theme
     *
     * @param string $areaCode
     * @return \Magento\Install\Controller\Action
     */
    protected function _initDefaultTheme($areaCode)
    {
        /** @var $themesCollection \Magento\Core\Model\Theme\Collection */
        $themesCollection = $this->_collectionFactory->create();
        $themeModel = $themesCollection->addDefaultPattern($areaCode)
            ->addFilter('theme_path', $this->_viewDesign->getConfigurationDesignTheme($areaCode))
            ->getFirstItem();
        $this->_viewDesign->setArea($areaCode)->setDesignTheme($themeModel);
        return $this;
    }
}
