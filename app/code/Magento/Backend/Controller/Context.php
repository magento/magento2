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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Controller;

/**
 * Controller context
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context extends \Magento\Core\Controller\Varien\Action\Context
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translator;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\App\ResponseInterface $response
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param $isRenderInherited
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Core\Model\Translate $translator
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Core\Model\LocaleInterface $locale
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\App\RequestInterface $request,
        \Magento\App\ResponseInterface $response,
        \Magento\ObjectManager $objectManager,
        \Magento\App\FrontController $frontController,
        \Magento\View\LayoutInterface $layout,
        \Magento\Event\ManagerInterface $eventManager,
        $isRenderInherited,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Helper\Data $helper,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Core\Model\Translate $translator,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Core\Model\LocaleInterface $locale
    ) {
        parent::__construct($logger, $request, $response, $objectManager, $frontController, $layout, $eventManager, 
            $isRenderInherited
        );
        $this->_session = $session;
        $this->_helper = $helper;
        $this->_authorization = $authorization;
        $this->_translator = $translator;
        $this->_auth = $auth;
        $this->_backendUrl = $backendUrl;
        $this->_locale = $locale;
    }

    /**
     * @return \Magento\Backend\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @return \Magento\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magento\Core\Model\Translate
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento\Backend\Model\Url
     */
    public function getBackendUrl()
    {
        return $this->_backendUrl;
    }

    /**
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }
}
