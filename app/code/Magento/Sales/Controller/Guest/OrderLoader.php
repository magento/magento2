<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
