<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Magento HTTP Client
 */
namespace Magento\Framework\HTTP;

use Laminas\Http\Client;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Traversable;

class LaminasClient extends Client implements ResetAfterRequestInterface
{
    /**
     * Internal flag to allow decoding of request body
     *
     * @var bool
     */
    protected bool $urlEncodeBody = true;

    /**
     * @param null|string $uri
     * @param null|array|Traversable $options
     */
    public function __construct($uri = null, $options = null)
    {
        $this->setOptions([
            'useragent' => Client::class,
            'adapter' => Curl::class,
        ]);

        parent::__construct($uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->reset();
        // Note: added these because LaminasClient doesn't properly reset them.
        $this->request = null;
        $this->encType = '';
    }

    /**
     * Change value of internal flag to disable/enable custom prepare functionality
     *
     * @param bool $flag
     * @return void
     */
    public function setUrlEncodeBody(bool $flag): void
    {
        $this->urlEncodeBody = $flag;
    }

    /**
     * @inheritdoc
     *
     * Adding custom functionality to decode data after standard prepare functionality
     */
    protected function prepareBody()
    {
        $body = parent::prepareBody();
        if (!$this->urlEncodeBody && $body) {
            $body = urldecode($body);
        }

        return $body;
    }
}
