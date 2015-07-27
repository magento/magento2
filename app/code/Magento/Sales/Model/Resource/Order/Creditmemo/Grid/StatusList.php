<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Creditmemo\Grid;

/**
 * Sales creditmemo statuses option array
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\Order\CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * @param \Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository
     */
    public function __construct(\Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository)
    {
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->creditmemoRepository->create()->getStates();
    }
}
