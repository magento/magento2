<?php
/**
<<<<<<< HEAD
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

=======
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
declare(strict_types=1);

namespace Magento\Customer\Model\Delegation;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AccountDelegationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
<<<<<<< HEAD
 * @inheritDoc
=======
 * {@inheritdoc}
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class AccountDelegation implements AccountDelegationInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param RedirectFactory $redirectFactory
     * @param Storage $storage
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Storage $storage
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->storage = $storage;
    }

    /**
<<<<<<< HEAD
     * @inheritDoc
=======
     * {@inheritdoc}
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function createRedirectForNew(
        CustomerInterface $customer,
        array $mixedData = null
    ): Redirect {
        $this->storage->storeNewOperation($customer, $mixedData);

<<<<<<< HEAD
        return $this->redirectFactory->create()
            ->setPath('customer/account/create');
=======
        return $this->redirectFactory->create()->setPath('customer/account/create');
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}
