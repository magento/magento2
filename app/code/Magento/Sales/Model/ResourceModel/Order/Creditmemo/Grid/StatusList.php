<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid;

/**
 * Sales creditmemo statuses option array
 * @since 2.0.0
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     * @since 2.0.0
     */
    protected $creditmemoRepository;

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @since 2.0.0
     */
    public function __construct(\Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository)
    {
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * Return option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->creditmemoRepository->create()->getStates();
    }
}
