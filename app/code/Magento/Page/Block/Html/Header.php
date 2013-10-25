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
 * @package     Magento_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Magento
 * @package    Magento_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Page\Block\Html;

class Header extends \Magento\Core\Block\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\App\State $appState
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\App\State $appState,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        parent::__construct($coreData, $context, $data);
    }

    public function _construct()
    {
        $this->setTemplate('html/header.phtml');
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
    }

    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = $this->_getLogoUrl();
        }
        return $this->_data['logo_src'];
    }

    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = $this->_storeConfig->getConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }

    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if ($this->_appState->isInstalled() && $this->_customerSession->isLoggedIn()) {
                $this->_data['welcome'] = __('Welcome, %1!', $this->escapeHtml($this->_customerSession->getCustomer()->getName()));
            } else {
                $this->_data['welcome'] = $this->_storeConfig->getConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }

    /**
     * Retrieve logo image URL
     *
     * @return string
     */
    protected function _getLogoUrl()
    {
        $folderName = \Magento\Backend\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_storeConfig->getConfig('design/header/logo_src');
        $logoUrl = $this->_urlBuilder->getBaseUrl(array('_type' => \Magento\Core\Model\Store::URL_TYPE_MEDIA))
            . $folderName . '/' . $storeLogoPath;
        $absolutePath = $this->_dirs->getDir(\Magento\App\Dir::MEDIA) . DIRECTORY_SEPARATOR
            . $folderName . DIRECTORY_SEPARATOR . $storeLogoPath;

        if (!is_null($storeLogoPath) && $this->_isFile($absolutePath)) {
            $url = $logoUrl;
        } else {
            $url = $this->getViewFileUrl('images/logo.gif');
        }

        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename
     * @return bool
     */
    protected function _isFile($filename)
    {
        $helper = $this->_helperFactory->get('Magento\Core\Helper\File\Storage\Database');

        if ($helper->checkDbUsage() && !is_file($filename)) {
            $helper->saveFileToFilesystem($filename);
        }

        return is_file($filename);
    }
}
