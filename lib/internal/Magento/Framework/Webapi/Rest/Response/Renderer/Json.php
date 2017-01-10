<?php
/**
 *  JSON Renderer allows to format array or object as JSON document.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest\Response\Renderer;

class Json implements \Magento\Framework\Webapi\Rest\Response\RendererInterface
{
    /**
     * Adapter mime type.
     */
    const MIME_TYPE = 'application/json';

    /** @var \Magento\Framework\Json\Encoder */
    protected $encoder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Json\Encoder $encoder
     */
    public function __construct(\Magento\Framework\Json\Encoder $encoder)
    {
        $this->encoder= $encoder;
    }

    /**
     * Convert data to JSON.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     */
    public function render($data)
    {
        return $this->encoder->encode($data);
    }

    /**
     * Get JSON renderer MIME type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return self::MIME_TYPE;
    }
}
