<?php

declare(strict_types=1);

namespace Chizhov\Status\Api\Data;

/** @api */
interface CustomerStatusInterface
{
    /**@#+
     * The keys of data array.
     */
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_STATUS = 'customer_status';
    /**@#-*/

    /**
     * Get customer ID.
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Get customer status.
     *
     * @return string|null
     */
    public function getCustomerStatus(): ?string;

    /**
     * Set customer ID.
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self;

    /**
     * Set customer status.
     *
     * @param string|null $status
     * @return $this
     */
    public function setCustomerStatus(?string $status): self;
}
