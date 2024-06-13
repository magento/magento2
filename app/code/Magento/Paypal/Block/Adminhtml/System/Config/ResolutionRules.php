<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Paypal\Model\Config\Rules\Reader;

/**
 * Class ResolutionRules
 *
 * @api
 * @since 100.0.2
 */
class ResolutionRules extends Template
{
    /**
     * @var Reader
     */
    private $rulesReader;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Reader $rulesReader
     * @param array $data
     */
    public function __construct(
        Context $context,
        Reader $rulesReader,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rulesReader = $rulesReader;
    }

    /**
     * Getting data for generating rules (JSON)
     *
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->rulesReader->read(), JSON_FORCE_OBJECT);
    }
}
