<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     */
    public function prepare()
    {
        if ($this->getData('config/uploaderConfig/url')) {
            $this->setData(
                array_replace_recursive(
                    $this->getData(),
                    [
                        'config' => [
                            'uploaderConfig' => [
                                'url' => $this->getContext()->getUrl(
                                    $this->getData('config/uploaderConfig/url'),
                                    ['_secure' => true]
                                )
                            ],
                        ],
                    ]
                )
            );
        }
        parent::prepare();
    }
}
