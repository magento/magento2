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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Abstract installation block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Block;

abstract class AbstractBlock extends \Magento\View\Element\Template
{
    /**
     * Installer model
     *
     * @var \Magento\Install\Model\Installer
     */
    protected $_installer;

    /**
     * Wizard model
     *
     * @var \Magento\Install\Model\Wizard
     */
    protected $_installWizard;

    /**
     * Install session
     *
     * @var \Magento\Session\Generic
     */
    protected $_session;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Install\Model\Wizard $installWizard
     * @param \Magento\Session\Generic $session
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Install\Model\Installer $installer,
        \Magento\Install\Model\Wizard $installWizard,
        \Magento\Session\Generic $session,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_installer = $installer;
        $this->_installWizard = $installWizard;
        $this->_session = $session;
        $this->_isScopePrivate = true;
    }


    /**
     * Retrieve installer model
     *
     * @return \Magento\Install\Model\Installer
     */
    public function getInstaller()
    {
        return $this->_installer;
    }
    
    /**
     * Retrieve wizard model
     *
     * @return \Magento\Install\Model\Wizard
     */
    public function getWizard()
    {
        return $this->_installWizard;
    }
    
    /**
     * Retrieve current installation step
     *
     * @return \Magento\Object
     */
    public function getCurrentStep()
    {
        return $this->getWizard()->getStepByRequest($this->getRequest());
    }
}
