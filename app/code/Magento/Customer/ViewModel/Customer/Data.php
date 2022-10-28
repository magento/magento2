<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\Serializer\Json as Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer's data view model
 */
class Data implements ArgumentInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Json
     */
    private $jsonEncoder;

    /**
     * @param CustomerSession $customerSession
     * @param Json $jsonEncoder
     */
    public function __construct(
        CustomerSession $customerSession,
        Json $jsonEncoder
    ) {
        $this->customerSession = $customerSession;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Check is user login
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool) $this->customerSession->isLoggedIn();
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public function jsonEncode($valueToEncode)
    {
        return $this->jsonEncoder->serialize($valueToEncode);
    }
}
