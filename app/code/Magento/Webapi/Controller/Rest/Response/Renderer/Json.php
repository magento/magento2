<?php
/**
 *  JSON Renderer allows to format array or object as JSON document.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class Json implements \Magento\Webapi\Controller\Rest\Response\RendererInterface
{
    /**
     * Adapter mime type.
     */
    const MIME_TYPE = 'application/json';

    /** @var \Magento\Core\Helper\Data */
    protected $_helper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Core\Helper\Data $helper
     */
    public function __construct(\Magento\Core\Helper\Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Convert data to JSON.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     */
    public function render($data)
    {
        return $this->_helper->jsonEncode($data);
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
