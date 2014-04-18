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
namespace Magento\Backend\App\Response\Http;

class FileFactory extends \Magento\Framework\App\Response\Http\FileFactory
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_flag;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\App\ActionFlag $flag
     * @param \Magento\Backend\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\App\ActionFlag $flag,
        \Magento\Backend\Helper\Data $helper
    ) {
        $this->_auth = $auth;
        $this->_backendUrl = $backendUrl;
        $this->_session = $session;
        $this->_flag = $flag;
        $this->_helper = $helper;
        parent::__construct($response, $filesystem);
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return \Magento\Framework\App\ResponseInterface
     * @TODO move method
     */
    protected function _redirect($path, $arguments = array())
    {
        $this->_session->setIsUrlNotice(
            $this->_flag->get('', \Magento\Backend\App\AbstractAction::FLAG_IS_URLS_CHECKED)
        );
        $this->_response->setRedirect($this->_helper->getUrl($path, $arguments));
        return $this->_response;
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     * that case
     * @param string $baseDir
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function create(
        $fileName,
        $content,
        $baseDir = \Magento\Framework\App\Filesystem::ROOT_DIR,
        $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        if ($this->_auth->getAuthStorage()->isFirstPageAfterLogin()) {
            return $this->_redirect($this->_backendUrl->getStartupPageUrl());
        }
        return parent::create($fileName, $content, $baseDir, $contentType, $contentLength);
    }
}
