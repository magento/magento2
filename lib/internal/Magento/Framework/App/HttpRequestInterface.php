<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Interface HttpRequestInterface
 *
 * @api
 */
interface HttpRequestInterface
{
    /**
     * Returned true if POST request
     *
     * @return boolean
     */
    public function isPost();

    /**
     * Returned true if GET request
     *
     * @return boolean
     */
    public function isGet();

    /**
     * Returned true if PATCH request
     *
     * @return boolean
     */
    public function isPatch();

    /**
     * Returned true if DELETE request
     *
     * @return boolean
     */
    public function isDelete();

    /**
     * Returned true if PUT request
     *
     * @return boolean
     */
    public function isPut();

    /**
     * Returned true if Ajax request
     *
     * @return boolean
     */
    public function isAjax();
}
