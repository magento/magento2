<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface provides low-level access to Magento Application Request and represent it as a simple string.
 * This interface does not define format of the request content.
 * Clients of this interface must be able to validate syntax of request and parse it.
 *
 * To read already parsed request data use \Magento\Framework\App\RequestInterface.
 *
 * @api
 * @since 100.2.0
 */
interface PlainTextRequestInterface
{
    /**
     * Returns textual representation of request to Magento.
     *
     * @return string
     * @since 100.2.0
     */
    public function getContent();
}
