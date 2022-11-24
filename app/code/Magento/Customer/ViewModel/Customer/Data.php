<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Serialize\Serializer\Json as Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer's data view model
 */
class Data implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $jsonEncoder;

    /**
     *
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param HttpContext $httpContext
     * @param Json $jsonEncoder
     */
    public function __construct(
        HttpContext $httpContext,
        Json $jsonEncoder
    ) {
        $this->httpContext = $httpContext;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Check is user login
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
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
