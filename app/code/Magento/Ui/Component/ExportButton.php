<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ExportButton
 * @since 2.0.0
 */
class ExportButton extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @since 2.0.0
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
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
