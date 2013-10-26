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


/**
 * Install index controller
 */
namespace Magento\Install\Controller;

class Index extends \Magento\Install\Controller\Action
{
    /**
     * Core directory model
     *
     * @var \Magento\App\Dir
     */
    protected $_coreDir;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\State $appState
     * @param \Magento\App\Dir $coreDir
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Config\Scope $configScope,
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Theme\CollectionFactory $collectionFactory,
        \Magento\Core\Model\App $app,
        \Magento\App\State $appState,
        \Magento\App\Dir $coreDir
    ) {
        parent::__construct($context, $configScope, $viewDesign, $collectionFactory, $app, $appState);
        $this->_coreDir = $coreDir;
    }

    /**
     * Dispatch event before action
     */
    public function preDispatch()
    {
        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, true);
        if (!$this->_appState->isInstalled()) {
            foreach (glob($this->_coreDir->getDir(\Magento\App\Dir::VAR_DIR) . '/*', GLOB_ONLYDIR) as $dir) {
                \Magento\Io\File::rmdirRecursive($dir);
            }
        }
        parent::preDispatch();
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_forward('begin', 'wizard', 'install');
    }
}
