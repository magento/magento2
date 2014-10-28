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
namespace Magento\Install\Block;

/**
 * Install state block
 */
class State extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'state.phtml';

    /**
     * Install Wizard
     *
     * @var \Magento\Install\Model\Wizard
     */
    protected $_wizard;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Install\Model\Wizard $wizard
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Install\Model\Wizard $wizard,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->assign('steps', $wizard->getSteps());
    }

    /**
     * Get previous downloader steps
     *
     * @return array
     */
    public function getDownloaderSteps()
    {
        if ($this->isDownloaderInstall()) {
            $steps = array(__('Welcome'), __('Validation'), __('Magento Connect Manager Deployment'));
            return $steps;
        } else {
            return array();
        }
    }

    /**
     * Checks for Magento Connect Manager installation method
     *
     * @return bool
     */
    public function isDownloaderInstall()
    {
        $session = $this->_request->getCookie('magento_downloader_session', false);
        return $session ? true : false;
    }
}
