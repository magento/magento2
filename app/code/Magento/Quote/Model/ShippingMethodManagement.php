<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;

/**
 * Shipping method read service.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagement implements
    \Magento\Quote\Api\ShippingMethodManagementInterface,
    \Magento\Quote\Model\ShippingMethodManagementInterface,
    ShipmentEstimationInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * Data object processor for array serialization using class reflection.
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * Customer address interface factory.
     *
     * @var AddressInterfaceFactory $addressFactory
     */
    private $addressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * Constructs a shipping method read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Quote\TotalsCollector $totalsCollector
     * @param DataObjectProcessor|null $dataProcessor
     * @param AddressInterfaceFactory|null $addressFactory
     * @param QuoteAddressResource|null $quoteAddressResource
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        DataObjectProcessor $dataProcessor = null,
        AddressInterfaceFactory $addressFactory = null,
        QuoteAddressResource $quoteAddressResource = null
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->addressRepository = $addressRepository;
        $this->totalsCollector = $totalsCollector;
        $this->dataProcessor = $dataProcessor ?: ObjectManager::getInstance()->get(DataObjectProcessor::class);
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()->get(AddressInterfaceFactory::class);
        $this->quoteAddressResource = $quoteAddressResource ?: ObjectManager::getInstance()
            ->get(QuoteAddressResource::class);
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if (!$shippingMethod) {
            return null;
        }

        $shippingAddress->collectShippingRates();
        /** @var \Magento\Quote\Model\Quote\Address\Rate $shippingRate */
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if (!$shippingRate) {
            return null;
        }
        return $this->converter->modelToDataObject($shippingRate, $quote->getQuoteCurrencyCode());
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        $output = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            $this->apply($cartId, $carrierCode, $methodCode);
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Cannot set shipping method. %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return void
     * @throws InputException The shipping method is not valid for an empty cart.
     * @throws NoSuchEntityException Cart contains only virtual products. Shipping method is not applicable.
     * @throws StateException The billing or shipping address is not set.
     * @throws \Exception
     */
    public function apply($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (0 == $quote->getItemsCount()) {
            throw new InputException(__('Shipping method is not applicable for empty cart'));
        }
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping method is not applicable.')
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            // Remove empty quote address
            $this->quoteAddressResource->delete($shippingAddress);
            throw new StateException(__('Shipping address is not set'));
        }
        $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get estimated rates
     *
     * @param Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @param \Magento\Framework\Api\ExtensibleDataInterface|null $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated
     */
    protected function getEstimatedRates(
        \Magento\Quote\Model\Quote $quote,
        $country,
        $postcode,
        $regionId,
        $region,
        $address = null
    ) {
        if (!$address) {
            $address = $this->addressFactory->create()
                ->setCountryId($country)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region);
        }

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get list of available shipping methods
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, $address)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->extractAddressData($address));
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * Get transform address interface into Array.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }

        return $this->dataProcessor->buildOutputDataArray(
            $address,
            $className
        );
    }
}
