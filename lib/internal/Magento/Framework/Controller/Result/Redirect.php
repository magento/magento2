<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\UrlInterface;

/**
 * In many cases controller actions may result in a redirect
 * so this is a result object that implements all necessary properties of a HTTP redirect
 *
 * @api
 * @since 100.0.2
 */
class Redirect extends AbstractResult
{

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var UrlInterface
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
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        App\Response\RedirectInterface $redirect,
        UrlInterface $urlBuilder
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
     * URL Setter
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
        if (empty($this->httpResponseCode)) {
            $response->setRedirect($this->url);
        } else {
            $response->setRedirect($this->url, $this->httpResponseCode);
        }
        return $this;
    }
}
