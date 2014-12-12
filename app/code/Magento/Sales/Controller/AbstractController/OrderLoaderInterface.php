<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface OrderLoaderInterface
{
    /**
     * Load order
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Magento\Sales\Model\Order
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
