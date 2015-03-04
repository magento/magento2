<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block;

use Magento\Framework\Translate\InlineInterface as InlineTranslator;
use Magento\Translation\Model\Js as DataProvider;
use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\Js\Config;

class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * Data provider model
     *
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * Inline translator
     *
     * @var InlineTranslator
     */
    protected $translateInline;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Template\Context $context
     * @param DataProvider $dataProvider
     * @param InlineTranslator $translateInline
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataProvider $dataProvider,
        InlineTranslator $translateInline,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataProvider = $dataProvider;
        $this->translateInline = $translateInline;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getTranslatedJson()
    {
        $data = $this->dataProvider->getTranslateData();
        $this->translateInline->processResponseBody($data);
        return \Zend_Json::encode($data);
    }

    /**
     * Is js translation set to dictionary mode
     *
     * @return bool
     */
    public function isDictionaryStrategy()
    {
        return $this->config->isDictionaryStrategy();
    }
}
