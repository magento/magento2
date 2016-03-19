<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\UrlInterface;

/**
 * Class SystemDataProvider
 */
class System extends AbstractModifier
{
    const KEY_SUBMIT_URL = 'submit_url';
    const KEY_VALIDATE_URL = 'validate_url';
    const KEY_RELOAD_URL = 'reloadUrl';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $productUrls = [
        self::KEY_SUBMIT_URL => 'catalog/product/save',
        self::KEY_VALIDATE_URL => 'catalog/product/validate',
        self::KEY_RELOAD_URL => 'catalog/product/reload'
    ];

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param array $productUrls
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        array $productUrls = []
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->productUrls = array_replace_recursive($this->productUrls, $productUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        $attributeSetId = $model->getAttributeSetId();

        $parameters = [
            'id' => $model->getId(),
            'type' => $model->getTypeId(),
            'store' => $model->getStoreId(),
        ];
        $actionParameters = array_merge($parameters, ['set' => $attributeSetId]);
        $reloadParameters = array_merge(
            $parameters,
            [
                'popup' => 1,
                'componentJson' => 1,
                'prev_set_id' => $attributeSetId,
                'type' => $this->locator->getProduct()->getTypeId()
            ]
        );

        $submitUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_SUBMIT_URL], $actionParameters);
        $validateUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_VALIDATE_URL], $actionParameters);
        $reloadUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_RELOAD_URL], $reloadParameters);

        return array_replace_recursive(
            $data,
            [
                'config' => [
                    self::KEY_SUBMIT_URL => $submitUrl,
                    self::KEY_VALIDATE_URL => $validateUrl,
                    self::KEY_RELOAD_URL => $reloadUrl,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
