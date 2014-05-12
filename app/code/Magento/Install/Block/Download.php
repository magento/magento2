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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Download Magento core modules and updates choice (online, offline)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Block;

class Download extends \Magento\Install\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'download.phtml';

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Install\Model\Wizard $installWizard
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Install\Model\Installer $installer,
        \Magento\Install\Model\Wizard $installWizard,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        array $data = array()
    ) {
        parent::__construct($context, $installer, $installWizard, $session, $data);
        $this->_moduleReader = $moduleReader;
    }

    /**
     * Retrieve locale data post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/downloadPost');
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->_installWizard->getStepByName('download')->getNextUrl();
    }

    /**
     * @return bool
     */
    public function hasLocalCopy()
    {
        $path = $this->_moduleReader->getModuleDir('etc', 'Magento_Adminhtml');
        $directory = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MODULES_DIR);

        if ($path && $directory->isDirectory($directory->getRelativePath($path))) {
            return true;
        }
        return false;
    }
}
