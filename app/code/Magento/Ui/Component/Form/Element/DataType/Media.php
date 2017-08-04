<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Media
 */
class Media extends AbstractDataType
{
    const NAME = 'media';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.11
     */
    public function prepare()
    {
        if ($this->getData('config/uploaderConfig/url')) {
            $url = $this->getContext()->getUrl($this->getData('config/uploaderConfig/url'), ['_secure' => true]);
            $data = array_replace_recursive(
                $this->getData(),
                [
                    'config' => [
                        'uploaderConfig' => [
                            'url' => $url
                        ],
                    ],
                ]
            );
            $this->setData($data);
        }
        parent::prepare();
    }
}
