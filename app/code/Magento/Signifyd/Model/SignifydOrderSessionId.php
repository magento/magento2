<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;

/**
 * Encapsulates generation of uuid by quote id.
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class SignifydOrderSessionId
{
    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(
        IdentityGeneratorInterface $identityGenerator
    ) {
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Returns unique identifier through generation uuid by quote id.
     *
     * @param int $quoteId
     * @return string
     */
    public function get($quoteId)
    {
        return $this->identityGenerator->generateIdForData($quoteId);
    }
}
