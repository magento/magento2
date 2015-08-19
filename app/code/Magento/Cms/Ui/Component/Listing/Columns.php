<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

class  Columns extends \Magento\Ui\Component\Listing\Columns
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Build correct url for inline editing
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        if (isset($config['editorConfig']) && isset($config['editorConfig']['clientConfig'])) {
            foreach ($config['editorConfig']['clientConfig'] as $key => &$value) {
                if (in_array($key, ['saveUrl', 'validateUrl'])) {
                    $value = $this->urlBuilder->getUrl($value);
                }
            }
        }
        $this->setData('config', $config);
        parent::prepare();
    }
}
