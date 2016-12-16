<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Signifyd\Model\QuoteSession\QuoteSessionInterface;

/**
 * Class SessionId generate uuid by quote id.
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
     * Generates unique identifier by quote id.
     *
     * @return string
     */
    public function generate()
    {
        return $this->identityGenerator->generateIdForData(
            $this->quoteSession->getQuote()->getId()
        );
    }
}
