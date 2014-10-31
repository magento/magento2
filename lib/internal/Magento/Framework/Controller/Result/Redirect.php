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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\App;

/**
 * In many cases controller actions may result in a redirect
 * so this is a result object that implements all necessary properties of a HTTP redirect
 */
class Redirect extends AbstractResult
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $url;

    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Set url from referer
     *
     * @return $this
     */
    public function setRefererUrl()
    {
        $this->url = $this->redirect->getRefererUrl();
        return $this;
    }

    /**
     * Set referer url or base if referer is not exist
     *
     * @return $this
     */
    public function setRefererOrBaseUrl()
    {
        $this->url = $this->redirect->getRedirectUrl();
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set url by path
     *
     * @param string $path
     * @param array $params
     * @return $this
     */
    public function setPath($path, array $params = [])
    {
        $this->url = $this->urlBuilder->getUrl($path, $this->redirect->updatePathParams($params));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(App\ResponseInterface $response)
    {
        $response->setRedirect($this->url);
        return $this;
    }
}
