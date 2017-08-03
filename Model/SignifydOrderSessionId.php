<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;

/**
 * Encapsulates generation of uuid by quote id.
 * @since 2.2.0
 */
class SignifydOrderSessionId
{
    /**
     * @var IdentityGeneratorInterface
     * @since 2.2.0
     */
    private $identityGenerator;

    /**
     * @param IdentityGeneratorInterface $identityGenerator
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function get($quoteId)
    {
        return $this->identityGenerator->generateIdForData($quoteId);
    }
}
