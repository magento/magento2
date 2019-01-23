<?php

declare(strict_types=1);

namespace Chizhov\Status\Model;

use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerStatus extends AbstractModel implements CustomerStatusInterface
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = CustomerStatusInterface::CUSTOMER_ID;

    /**
     * @inheritDoc
     */
    protected $_mainTable = 'chizhov_customer_status';

    /**
     * @inheritDoc
     */
    protected $_eventPrefix = 'chizhov_customer_status';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\CustomerStatus::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        $id = $this->getId();

        return isset($id) ? (int)$id : null;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerStatus(): ?string
    {
        return $this->getData(CustomerStatusInterface::CUSTOMER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(?int $customerId): CustomerStatusInterface
    {
        $this->setId($customerId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerStatus(?string $status): CustomerStatusInterface
    {
        $this->setData(CustomerStatusInterface::CUSTOMER_STATUS, $status);

        return $this;
    }
}
