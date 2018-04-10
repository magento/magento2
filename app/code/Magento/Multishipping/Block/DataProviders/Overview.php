<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Block\DataProviders;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Provides additional data for multishipping checkout overview step.
 */
class Overview implements ArgumentInterface
{
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var array
     */
    private $addressErrors = [];

    /**
     * @param SessionManagerInterface $session
     */
    public function __construct(
        SessionManagerInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * Returns address error.
     *
     * @param Address $address
     * @return string
     */
    public function getAddressError(Address $address): string
    {
        $addressErrors = $this->getAddressErrors();

        return $addressErrors[$address->getId()] ?? '';
    }

    /**
     * Returns all stored errors.
     *
     * @return array
     */
    public function getAddressErrors(): array
    {
        if (empty($this->addressErrors)) {
            $this->addressErrors = $this->session->getAddressErrors(true);
        }

        return $this->addressErrors ?? [];
    }

    /**
     * Creates anchor name for address Id.
     *
     * @param int $addressId
     * @return string
     */
    public function getAddressAnchorName(int $addressId): string
    {
        return 'a' . $addressId;
    }
}
