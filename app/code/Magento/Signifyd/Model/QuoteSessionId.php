<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Signifyd\Model\QuoteSession\QuoteSessionInterface;

/**
 * Class SessionId encapsulate generation of uuid by quote id.
 */
class QuoteSessionId
{
    /**
     * @var QuoteSessionInterface
     */
    private $quoteSession;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * QuoteSessionId constructor.
     *
     * Class uses identity generator for uuid creation.
     *
     * @param QuoteSessionInterface $quoteSession
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(
        QuoteSessionInterface $quoteSession,
        IdentityGeneratorInterface $identityGenerator
    ) {
        $this->quoteSession = $quoteSession;
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Gets unique identifier through generation uuid by quote id.
     *
     * @param int|null $quoteId
     * @return string
     */
    public function get($quoteId = null)
    {
        return $this->identityGenerator->generateIdForData(
            $quoteId ? : $this->quoteSession->getQuote()->getId()
        );
    }
}
