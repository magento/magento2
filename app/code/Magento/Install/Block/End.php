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
 * Installation ending block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Block;

class End extends \Magento\Install\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'end.phtml';

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\AdminNotification\Model\Survey
     */
    protected $_survey;

    /**
     * Cryptographic key
     *
     * @var string
     */
    protected $_cryptKey;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Install\Model\Wizard $installWizard
     * @param \Magento\Session\Generic $session
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param \Magento\AdminNotification\Model\Survey $survey
     * @param string $cryptKey
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Install\Model\Installer $installer,
        \Magento\Install\Model\Wizard $installWizard,
        \Magento\Session\Generic $session,
        \Magento\App\ConfigInterface $coreConfig,
        \Magento\AdminNotification\Model\Survey $survey,
        $cryptKey,
        array $data = array()
    ) {
        $this->_cryptKey = $cryptKey;
        parent::__construct($context, $installer, $installWizard, $session, $data);
        $this->_coreConfig = $coreConfig;
        $this->_survey = $survey;
    }

    /**
     * @return string
     */
    public function getEncryptionKey()
    {
        $key = $this->getData('encryption_key');
        if (is_null($key)) {
            $key = $this->_cryptKey;
            $this->setData('encryption_key', $key);
        }
        return $key;
    }

    /**
     * Return url for iframe source
     *
     * @return string|null
     */
    public function getIframeSourceUrl()
    {
        if (!$this->_survey->isSurveyUrlValid() || $this->_installer->getHideIframe()) {
            return null;
        }
        return $this->_survey->getSurveyUrl();
    }
}
