<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class OrderSessionId
 */
class OrderSessionId
{
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @param SessionManagerInterface $session
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(
        SessionManagerInterface $session,
        IdentityGeneratorInterface $identityGenerator
    ) {
        $this->session = $session;
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Generate the unique ID for the user's browsing session
     *
     * @return string
     */
    public function generate()
    {
        return $this->identityGenerator->generateIdForData(
            $this->getQuote()->getId() . $this->getQuote()->getCreatedAt()
        );
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->session->getQuote();
        }

        return $this->quote;
    }
}
