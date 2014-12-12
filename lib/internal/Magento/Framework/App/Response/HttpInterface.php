<?php
/**
 * HTTP response interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Response;

interface HttpInterface extends \Magento\Framework\App\ResponseInterface
{
    /**
     * Set HTTP response code
     *
     * @param int $code
     * @return void
     */
    public function setHttpResponseCode($code);
}
