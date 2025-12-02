<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid;

/**
 * Sales creditmemo statuses option array
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(\Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository)
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
