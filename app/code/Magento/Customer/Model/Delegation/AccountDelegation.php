<?php
/**
<<<<<<< HEAD
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
=======
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

>>>>>>> upstream/2.2-develop
declare(strict_types=1);

namespace Magento\Customer\Model\Delegation;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AccountDelegationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
<<<<<<< HEAD
 * {@inheritdoc}
=======
 * @inheritDoc
>>>>>>> upstream/2.2-develop
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
     * {@inheritdoc}
=======
     * @inheritDoc
>>>>>>> upstream/2.2-develop
     */
    public function createRedirectForNew(
        CustomerInterface $customer,
        array $mixedData = null
    ): Redirect {
        $this->storage->storeNewOperation($customer, $mixedData);

<<<<<<< HEAD
        return $this->redirectFactory->create()->setPath('customer/account/create');
=======
        return $this->redirectFactory->create()
            ->setPath('customer/account/create');
>>>>>>> upstream/2.2-develop
    }
}
