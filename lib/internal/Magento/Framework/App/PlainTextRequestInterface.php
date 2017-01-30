<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved
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
 */
interface PlainTextRequestInterface
{
    /**
     * Returns textual representation of request to Magento.
     *
     * @return string
     */
    public function getContent();
}
