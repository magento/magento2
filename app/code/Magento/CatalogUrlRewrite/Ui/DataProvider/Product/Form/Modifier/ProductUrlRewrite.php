<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class adds a checkbox "url_key_create_redirect" after input "url_key" for product form
 */
class ProductUrlRewrite extends AbstractModifier
{
    const XML_PATH_SEO_SAVE_HISTORY = 'catalog/seo/save_rewrites_history';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getId()) {
            $meta = $this->addUrlRewriteCheckbox($meta);
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Adding URL rewrite checkbox to meta
     *
     * @param array $meta
     * @return array
     */
    protected function addUrlRewriteCheckbox(array $meta)
    {
        $urlPath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY,
            $meta,
            null,
            'children'
        );

        if ($urlPath) {
            $containerPath = $this->arrayManager->slicePath($urlPath, 0, -2);
            $urlKey = $this->locator->getProduct()->getData('url_key');
            $saveRewritesHistory = $this->scopeConfig->isSetFlag(
                self::XML_PATH_SEO_SAVE_HISTORY,
                ScopeInterface::SCOPE_STORE,
                $this->locator->getProduct()->getStoreId()
            );

            $meta = $this->arrayManager->merge($containerPath, $meta, [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Magento_Ui/js/form/components/group',
                        ],
                    ],
                ],
            ]);

            $checkbox['arguments']['data']['config'] = [
                'componentType' => Field::NAME,
                'formElement' => Checkbox::NAME,
                'dataType' => Text::NAME,
                'component' => 'Magento_Catalog/js/components/url-key-handle-changes',
                'valueMap' => [
                    'false' => '',
                    'true' => $urlKey
                ],
                'imports' => [
                    'urlKey' => '${ $.provider }:data.product.' . ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY,
                    'handleUseDefault' => '${ $.parentName }.url_key:isUseDefault',
                    'handleChanges' => '${ $.provider }:data.product.'
                        . ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY,
                ],
                'description' => __('Create Permanent Redirect for old URL'),
                'dataScope' => 'url_key_create_redirect',
                'value' => $saveRewritesHistory ? $urlKey : '',
                'checked' => $saveRewritesHistory,
            ];

            $meta = $this->arrayManager->merge(
                $urlPath . '/arguments/data/config',
                $meta,
                ['valueUpdate' => 'keyup']
            );
            $meta = $this->arrayManager->merge(
                $containerPath . '/children',
                $meta,
                ['url_key_create_redirect' => $checkbox]
            );
            $meta = $this->arrayManager->merge(
                $containerPath . '/arguments/data/config',
                $meta,
                ['breakLine' => true]
            );
        }

        return $meta;
    }
}
