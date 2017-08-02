<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller;

use Magento\Framework\App\ResponseInterface;

/**
 * An abstraction of result that controller actions must return
 * The point of this kind of object is to encapsulate all information/objects relevant to the result
 * and be able to set it to the HTTP response
 *
 * @api
 * @since 2.0.0
 */
interface ResultInterface
{
    /**
     * @param int $httpCode
     * @return $this
     * @since 2.0.0
     */
    public function setHttpResponseCode($httpCode);

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return $this
     * @since 2.0.0
     */
    public function setHeader($name, $value, $replace = false);

    /**
     * Render result and set to response
     *
     * @param ResponseInterface $response
     * @return $this
     * @since 2.0.0
     */
    public function renderResult(ResponseInterface $response);
}
