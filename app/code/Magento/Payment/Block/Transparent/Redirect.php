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
 *
 * @api
 * @since 100.3.5
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
     * @since 100.3.5
     */
    public function getRedirectUrl(): string
    {
        return $this->url->getUrl($this->getData(self::ROUTE_PATH));
    }

    /**
     * Returns params to be redirected.
     *
     * Encodes invalid UTF-8 values to UTF-8 to prevent character escape error.
     * Some payment methods like PayPal, send data in merchant defined language encoding
     * which can be different from the system character encoding (UTF-8).
     *
     * @return array
     * @since 100.3.5
     */
    public function getPostParams(): array
    {
        $params = [];
        foreach ($this->_request->getPostValue() as $name => $value) {
            if (!empty($value) && mb_detect_encoding($value, 'UTF-8', true) === false) {
                $value = utf8_encode($value);
            }
            $params[$name] = $value;
        }
        return $params;
    }
}
