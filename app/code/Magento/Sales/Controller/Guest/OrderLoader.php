<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

/**
 * Class \Magento\Sales\Controller\Guest\OrderLoader
 *
 * @since 2.0.0
 */
class OrderLoader implements OrderLoaderInterface
{
    /**
     * @var \Magento\Sales\Helper\Guest
     * @since 2.0.0
     */
    protected $guestHelper;

    /**
     * @param \Magento\Sales\Helper\Guest $guestHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Helper\Guest $guestHelper
    ) {
        $this->guestHelper = $guestHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function load(RequestInterface $request)
    {
        return $this->guestHelper->loadValidOrder($request);
    }
}
