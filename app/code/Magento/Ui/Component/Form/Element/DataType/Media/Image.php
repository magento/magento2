<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType\Media;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Image Form UI Component
 */
class Image extends Media
{
    const NAME = 'image';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $data = array_replace_recursive(
            $this->getData(),
            [
                'config' => [
                    'mediaGallery' => [
                        'openDialogUrl' => $this->getContext()->getUrl('cms/wysiwyg_images/index'),
                        'openDialogTitle' => $this->getData('openDialogTitle') ?: __('Insert Images...'),
                        'storeId' => $this->storeManager->getStore()->getId(),
                    ],
                ],
            ]
        );

        $this->setData($data);
        parent::prepare();
    }
}
