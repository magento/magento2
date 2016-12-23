<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block;

use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\OrderSessionId;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Fingerprint
 */
class Fingerprint extends Template
{
    /**
     * @var OrderSessionId
     */
    private $orderSessionId;

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
     * @param OrderSessionId $orderSessionId
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        OrderSessionId $orderSessionId,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderSessionId = $orderSessionId;
        $this->config = $config;
    }

    /**
     * Retrieves per-order session id.
     *
     * @return string
     */
    public function getOrderSessionId()
    {
        return $this->orderSessionId->generate();
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
