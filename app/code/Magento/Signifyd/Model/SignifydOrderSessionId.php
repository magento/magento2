<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;

/**
 * Encapsulates generation of uuid by quote id.
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
