<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

class OrderLoader implements OrderLoaderInterface
{
    /**
     * @var \Magento\Sales\Helper\Guest
     */
    protected $guestHelper;

    /**
     * @param \Magento\Sales\Helper\Guest $guestHelper
     */
    public function __construct(
        \Magento\Sales\Helper\Guest $guestHelper
    ) {
        $this->guestHelper = $guestHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RequestInterface $request)
    {
        return $this->guestHelper->loadValidOrder($request);
    }
}
