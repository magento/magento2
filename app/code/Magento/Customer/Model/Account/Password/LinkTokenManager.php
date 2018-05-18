<?php
/**
 * @package  Magento\Customer
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Magento\Customer\Model\Account\Password;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\LinkTokenManagerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class LinkTokenManager
 */
class LinkTokenManager implements LinkTokenManagerInterface
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DateTime\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * PasswordLinkToken constructor.
     *
     * @param CustomerRegistry $customerRegistry
     * @param DateTimeFactory|DateTime\DateTimeFactory $dateTimeFactory
     * @param CustomerResource $customerResource
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        DateTimeFactory $dateTimeFactory,
        CustomerResource $customerResource
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerResource = $customerResource;
    }

    /**
     * Change and store new reset password link token
     *
     * @param CustomerInterface $customerData
     * @param string $passwordLinkToken
     *
     * @throws InputException
     *
     * @return bool
     */
    public function changeToken(CustomerInterface $customerData, string $passwordLinkToken): bool
    {
        if (empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }

        $customer = $this->customerRegistry->retrieve($customerData->getId());

        $customer->setRpToken($passwordLinkToken);
        $customer->setRpTokenCreatedAt(
            $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
        );

        $this->customerResource->save($customer);

        return true;
    }
}
