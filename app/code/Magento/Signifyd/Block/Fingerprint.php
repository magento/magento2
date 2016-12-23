<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\QuoteSessionId;

/**
 * Class Fingerprint
 */
class Fingerprint extends Template
{
    /**
     * @var QuoteSessionId
     */
    private $quoteSessionId;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    protected $_template = 'fingerprint.phtml';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param QuoteSessionId $orderSessionId
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        QuoteSessionId $orderSessionId,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteSessionId = $orderSessionId;
        $this->config = $config;
    }

    /**
     * Retrieves per-order session id.
     *
     * @return string
     */
    public function getQuoteSessionId()
    {
        return $this->quoteSessionId->generate();
    }

    /**
     * Checks if module is enabled.
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->config->isEnabled();
    }
}
