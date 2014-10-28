<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Service\V1\Address\Billing;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Logger;
use \Magento\Sales\Model\QuoteRepository;
use \Magento\Sales\Model\Quote\AddressFactory;
use \Magento\Checkout\Service\V1\Address\Converter;
use \Magento\Checkout\Service\V1\Address\Validator;

/** Quote billing address write service object. */
class WriteService implements WriteServiceInterface
{
    /**
     * Validator.
     *
     * @var Validator
     */
    protected $addressValidator;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Address factory.
     *
     * @var AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Converter.
     *
     * @var Converter
     */
    protected $addressConverter;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Constructs a quote billing address service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param Converter $addressConverter Address converter.
     * @param Validator $addressValidator Address validator.
     * @param AddressFactory $quoteAddressFactory Quote address factory.
     * @param Logger $logger Logger.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Converter $addressConverter,
        Validator $addressValidator,
        AddressFactory $quoteAddressFactory,
        Logger $logger
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $addressData Billing address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     */
    public function setAddress($cartId, $addressData)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->get($cartId);
        /**
         * Address.
         *
         * @var \Magento\Sales\Model\Quote\Address $address
         */
        $address = $this->quoteAddressFactory->create();
        $this->addressValidator->validate($addressData);
        if ($addressData->getId()) {
            $address->load($addressData->getId());
        }
        $address = $this->addressConverter->convertDataObjectToModel($addressData, $address);
        $quote->setBillingAddress($address);
        $quote->setDataChanges(true);
        try {
            $quote->save();
        } catch (\Exception $e) {
            $this->logger->logException($e);
            throw new InputException('Unable to save address. Please, check input data.');
        }
        return $quote->getBillingAddress()->getId();
    }
}
