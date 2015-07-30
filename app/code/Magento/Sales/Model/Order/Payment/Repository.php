<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Resource\Metadata;

/**
 * Class Repository
 */
class Repository implements OrderPaymentRepositoryInterface
{
    /**
     * Magento\Sales\Model\Order\Payment\Transaction[]
     *
     * @var array
     */
    private $registry = [];

    /**
     * @var Metadata
     */
    protected $metaData;

    /**
     * @param Metadata $metaData
     */
    function __construct(Metadata $metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Lists order payments that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $criteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderPaymentSearchResultInterface Order payment search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria)
    {

    }

    /**
     * Loads a specified order payment.
     *
     * @param int $id The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {

            $entity = $this->metaData->getMapper()->load($this->metaData->getNewInstance(), $id);
            if (!$entity->getId()) {
                throw new NoSuchEntityException('Requested entity doesn\'t exist');
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Deletes a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        return true;
    }

    /**
     * Performs persist operations for a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function save(\Magento\Sales\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        return $entity;
    }

    /**
     * Creates new Order Payment instance.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Transaction interface.
     */
    public function create()
    {
        return $this->metaData->getNewInstance();
    }
}
