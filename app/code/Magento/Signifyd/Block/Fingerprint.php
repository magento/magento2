<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\QuoteSession\QuoteSessionInterface;
use Magento\Signifyd\Model\SignifydOrderSessionId;

/**
 * Provides data for Signifyd device fingerprinting script.
 *
 * Signifyd’s device fingerprinting solution uniquely tracks and identifies devices
 * used to transact on your site, increasing your protection from fraud.
 *
 * @api
 * @see https://www.signifyd.com/docs/api/#/reference/device-fingerprint/create-a-case
 * @since 100.2.0
 */
class Fingerprint extends Template
{
    /**
     * @var SignifydOrderSessionId
     */
    private $signifydOrderSessionId;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QuoteSessionInterface
     */
    private $quoteSession;

    /**
     * @var string
     * @since 100.2.0
     */
    protected $_template = 'fingerprint.phtml';

    /**
     * @param Context $context
     * @param Config $config
     * @param SignifydOrderSessionId $signifydOrderSessionId
     * @param QuoteSessionInterface $quoteSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        SignifydOrderSessionId $signifydOrderSessionId,
        QuoteSessionInterface $quoteSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->signifydOrderSessionId = $signifydOrderSessionId;
        $this->config = $config;
        $this->quoteSession = $quoteSession;
    }

    /**
     * Returns a unique Signifyd order session id.
     *
     * @return string
     * @since 100.2.0
     */
    public function getSignifydOrderSessionId()
    {
        $quoteId = $this->quoteSession->getQuote()->getId();

        return $this->signifydOrderSessionId->get($quoteId);
    }

    /**
     * Checks if module is enabled.
     *
     * @return boolean
     * @since 100.2.0
     */
    public function isModuleActive()
    {
        return $this->config->isActive();
    }
}
