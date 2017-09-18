<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

/**
 * In many cases controller actions may result in a redirect
 * so this is a result object that implements all necessary properties of a HTTP redirect
 *
 * @api
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
    protected function render(HttpResponseInterface $response)
    {
        $response->setRedirect($this->url);
        return $this;
    }
}
