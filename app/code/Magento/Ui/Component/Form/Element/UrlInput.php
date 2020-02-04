<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element;

/**
 * Url Input to process data for urlInput component
 */
class UrlInput extends \Magento\Ui\Component\Form\Element\AbstractElement
{
    const NAME = 'urlInput';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(): void
    {
        $config = $this->getData('config');
        //process urlTypes
        if (isset($config['urlTypes'])) {
            $links = $config['urlTypes']->getConfig();
            $config['urlTypes'] = $links;
        }
        $this->setData('config', (array)$config);
        parent::prepare();
    }
}
