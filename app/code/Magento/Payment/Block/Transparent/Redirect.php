<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Redirect block for register specific params in layout
 */
class Redirect extends Template
{
    /**
     * Route path key to make redirect url.
     */
    private const ROUTE_PATH = 'route_path';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param Context $context
     * @param UrlInterface $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $url,
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * Returns url for redirect.
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->url->getUrl($this->getData(self::ROUTE_PATH));
    }

    /**
     * Returns params to be redirected.
     *
     * @return array
     */
    public function getPostParams(): array
    {
        return (array)$this->_request->getPostValue();
    }
}
