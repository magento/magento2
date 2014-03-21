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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\App\FrontController\Plugin;

class MessageBox
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'message_box_display';

    /**
     * Ten years cookie period
     */
    const COOKIE_PERIOD = 315360000;

    /**
     * Cookie
     *
     * @var \Magento\Stdlib\Cookie
     */
    protected $cookie;

    /**
     * Request
     *
     * @var \Magento\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Stdlib\Cookie $cookie
     * @param \Magento\App\Request\Http $request
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Stdlib\Cookie $cookie,
        \Magento\App\Request\Http $request,
        \Magento\PageCache\Model\Config $config,
        \Magento\Message\ManagerInterface $messageManager
    ) {
        $this->cookie = $cookie;
        $this->request = $request;
        $this->config = $config;
        $this->messageManager = $messageManager;
    }

    /**
     * Set Cookie for msg box when it displays first
     *
     * @param \Magento\App\FrontController $subject
     * @param \Magento\App\ResponseInterface $response
     *
     * @return \Magento\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(\Magento\App\FrontController $subject, \Magento\App\ResponseInterface $response)
    {
        if ($this->request->isPost() && $this->config->isEnabled() && $this->hasMessages()) {
            $this->cookie->set(self::COOKIE_NAME, 1, self::COOKIE_PERIOD, '/');
        }
        return $response;
    }

    /**
     * Returns true if there are any messages for customer,
     * false - in other case
     *
     * @return bool
     */
    protected function hasMessages()
    {
        return ($this->messageManager->getMessages()->getCount() > 0);
    }
}
