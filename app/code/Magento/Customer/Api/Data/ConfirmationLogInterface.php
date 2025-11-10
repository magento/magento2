<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Api\Data;

interface ConfirmationLogInterface
{
    /**
     * @var string
     */
    public const string CUSTOMER_ID = 'customer_id';

    /**
     * @var string
     */
    public const string EMAIL_SENT_COUNTER = 'email_sent_counter';

    /**
     * @var string
     */
    public const string LAST_EMAIL_SENT_AT = 'last_email_sent_at';

    /**
     * To get the customer id
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * To set the customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * To get the count of emails sent.
     *
     * @return int
     */
    public function getEmailSentCounter(): int;

    /**
     * To set the email sent counter value.
     *
     * @param int $counter
     * @return $this
     */
    public function setEmailSentCounter(int $counter): self;

    /**
     * To get the timestamp of the last email sent.
     *
     * @return string
     */
    public function getLastEmailSentAt(): string;

    /**
     * To set the timestamp of the last email sent.
     *
     * @param string $date
     * @return $this
     */
    public function setLastEmailSentAt(string $date): self;
}
