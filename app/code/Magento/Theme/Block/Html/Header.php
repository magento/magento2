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

namespace Magento\Theme\Block\Html;

use Magento\Customer\Helper\Data;
use Magento\Customer\Helper\View as CustomerViewHelper;

/**
 * Html page header block
 */
class Header extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'html/header.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_fileStorageHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerViewHelper $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Helper\File\Storage\Database $fileStorageHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerViewHelper $customerViewHelper,
        array $data = array()
    ) {
        $this->_fileStorageHelper = $fileStorageHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_customerViewHelper = $customerViewHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', array('_current' => true));
        $urlRewrite = $this->getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
        return $currentUrl == $urlRewrite;
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

    /**
     * Retrieve logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = $this->_scopeConfig->getValue(
                'design/header/logo_alt',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->_data['logo_alt'];
    }

    /**
     * Retrieve welcome text
     *
     * @return string
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if ($this->httpContext->getValue(Data::CONTEXT_AUTH)) {
                $customerName = $this->_customerViewHelper->getCustomerName(
                    $this->_customerSession->getCustomerDataObject()
                );
                $this->_data['welcome'] = __(
                    'Welcome, %1!',
                    $this->escapeHtml($customerName)
                );
            } else {
                $this->_data['welcome'] = $this->_scopeConfig->getValue(
                    'design/header/welcome',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
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
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->_urlBuilder
                ->getBaseUrl(array('_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA)) . $path;

        if (!is_null($storeLogoPath) && $this->_isFile($path)) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.gif');
        }
        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename relative path
     * @return bool
     */
    protected function _isFile($filename)
    {
        if ($this->_fileStorageHelper->checkDbUsage() && !$this->getMediaDirectory()->isFile($filename)) {
            $this->_fileStorageHelper->saveFileToFilesystem($filename);
        }

        return $this->getMediaDirectory()->isFile($filename);
    }
}
