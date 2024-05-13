<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\QuoteRepository;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\Address\ShippingAddressPersister;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Handler for saving quote.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SaveHandler
{
    /**
     * @var CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var BillingAddressPersister
     */
    private $billingAddressPersister;

    /**
     * @var Quote
     */
    private $quoteResourceModel;

    /**
     * @var ShippingAssignmentPersister
     */
    private $shippingAssignmentPersister;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @var ShippingAddressPersister
     */
    private $shippingAddressPersister;

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @param Quote $quoteResource
     * @param CartItemPersister $cartItemPersister
     * @param BillingAddressPersister $billingAddressPersister
     * @param ShippingAssignmentPersister $shippingAssignmentPersister
     * @param AddressRepositoryInterface|null $addressRepository
     * @param AddressInterfaceFactory|null $addressFactory
     * @param ShippingAddressPersister|null $shippingAddressPersister
     * @param QuoteSession|null $quoteSession
     */
    public function __construct(
        Quote $quoteResource,
        CartItemPersister $cartItemPersister,
        BillingAddressPersister $billingAddressPersister,
        ShippingAssignmentPersister $shippingAssignmentPersister,
        AddressRepositoryInterface $addressRepository = null,
        AddressInterfaceFactory $addressFactory = null,
        ShippingAddressPersister $shippingAddressPersister = null,
        QuoteSession $quoteSession = null
    ) {
        $this->quoteResourceModel = $quoteResource;
        $this->cartItemPersister = $cartItemPersister;
        $this->billingAddressPersister = $billingAddressPersister;
        $this->shippingAssignmentPersister = $shippingAssignmentPersister;
        $this->addressRepository = $addressRepository
            ?: ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $this->quoteAddressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
        $this->shippingAddressPersister = $shippingAddressPersister
            ?: ObjectManager::getInstance()->get(ShippingAddressPersister::class);
        $this->quoteSession = $quoteSession ?: ObjectManager::getInstance()->get(QuoteSession::class);
    }

    /**
     * Process and save quote data
     *
     * @param CartInterface $quote
     * @return CartInterface
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(CartInterface $quote)
    {
        // Quote Item processing
        $items = $quote->getItems();

        if ($items) {
            foreach ($items as $item) {
                if (!$item->isDeleted()) {
                    $quote->setLastAddedItem($this->cartItemPersister->save($quote, $item));
                } elseif (count($items) === 1) {
                    $quote->setBillingAddress($this->quoteAddressFactory->create());
                    $quote->setShippingAddress($this->quoteAddressFactory->create());
                }
            }
        }

        // Billing Address processing
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress) {
            $this->processAddress($billingAddress);
            $this->billingAddressPersister->save($quote, $billingAddress);
        }

        // Shipping Address processing
        if ($this->quoteSession->getData(('reordered'))) {
            $shippingAddress = $this->processAddress($quote->getShippingAddress());
            $this->shippingAddressPersister->save($quote, $shippingAddress);
        }

        $this->processShippingAssignment($quote);
        $this->quoteResourceModel->save($quote->collectTotals());

        return $quote;
    }

    /**
     * Process address for customer address Id
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws LocalizedException
     */
    private function processAddress(AddressInterface $address): AddressInterface
    {
        if ($address->getCustomerAddressId()) {
            try {
                $this->addressRepository->getById($address->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                $address->setCustomerAddressId(null);
            }
        }
        return $address;
    }

    /**
     * Process shipping assignment
     *
     * @param CartInterface $quote
     * @return void
     * @throws InputException
     */
    private function processShippingAssignment(CartInterface $quote)
    {
        // Shipping Assignments processing
        $extensionAttributes = $quote->getExtensionAttributes();

        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if (count($shippingAssignments) > 1) {
                throw new InputException(__('Only 1 shipping assignment can be set'));
            }

            $this->shippingAssignmentPersister->save($quote, $shippingAssignments[0]);
        }
    }
}
