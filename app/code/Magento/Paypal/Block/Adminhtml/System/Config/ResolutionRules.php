<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Paypal\Model\Config\Rules\Reader;

/**
 * Class ResolutionRules
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
