<?php
/**
 *  JSON Renderer allows to format array or object as JSON document.
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
