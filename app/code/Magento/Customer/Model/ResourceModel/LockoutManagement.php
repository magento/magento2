<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

use Magento\Backend\App\ConfigInterface;

class LockoutManagement extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * LockoutManagement constructor.
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param ConfigInterface $backendConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $data = []
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);
        $this->backendConfig = $backendConfig;
        $this->dateTime = $dateTime;
        $this->setType('customer');
        $this->setConnection('customer_read', 'customer_write');
    }

    /**
     * @param  \Magento\Customer\Model\Customer $customer
     * @return void
     */
    public function processLockout($customer)
    {
        $now = new \DateTime();
        $lockThreshold = $this->backendConfig->getValue('customer/password/lockout_threshold') * 60;
        $maxFailures = $this->backendConfig->getValue('customer/password/lockout_failures');
        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$customer->getFailuresNum() + 1;

        /** @noinspection PhpAssignmentInConditionInspection */
        if ($firstFailureDate = $customer->getFirstFailure()) {
            $firstFailureDate = new \DateTime($firstFailureDate);
        }

        $newFirstFailureDate = false;
        $updateLockExpires = false;
        $lockThreshInterval = new \DateInterval('PT' . $lockThreshold.'S');
        // set first failure date when this is first failure or last first failure expired
        if (1 === $failuresNum || !$firstFailureDate || $now->diff($firstFailureDate) > $lockThreshInterval) {
            $newFirstFailureDate = $now;
            // otherwise lock customer
        } elseif ($failuresNum >= $maxFailures) {
            $updateLockExpires = $now->add($lockThreshInterval);
        }
        $this->updateFailure($customer, $updateLockExpires, $newFirstFailureDate);
    }

    /**
     * Increment failures count along with updating lock expire and first failure dates
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param int|bool $setLockExpires
     * @param int|bool $setFirstFailure
     * @return void
     */
    protected function updateFailure($customer, $setLockExpires = false, $setFirstFailure = false)
    {
        $update = ['failures_num' => new \Zend_Db_Expr('failures_num + 1')];
        if (false !== $setFirstFailure) {
            $update['first_failure'] = $this->dateTime->formatDate($setFirstFailure);
            $update['failures_num'] = 1;
        }
        if (false !== $setLockExpires) {
            $update['lock_expires'] = $this->dateTime->formatDate($setLockExpires);
        }
        $this->getConnection()->update(
            $this->getTable('customer_entity'),
            $update,
            $this->getConnection()->quoteInto("{$this->getIdFieldName()} = ?", $customer->getId())
        );
    }

    /**
     * Unlock specified customer
     *
     * @param int $customerId
     * @return $this
     */
    public function unlock($customerId)
    {
        $this->getConnection()->update(
            $this->getTable('customer_entity'),
            ['failures_num' => 0, 'first_failure' => null, 'lock_expires' => null],
            $this->getIdFieldName() . ' = (' . $this->getConnection()->quote($customerId) . ')'
        );
        return $this;
    }
}
