<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfigurableProduct\Block\Adminhtml\Product\Steps;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextFactory;

class Bulk extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var \Magento\Framework\Config\DataInterfaceFactory
     */
    protected $configFactory;

    /**
     * @var \Magento\Framework\Config\DataInterfaceFactory
     */
    protected $uiComponentFactory;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    public function __construct(
        Context $context,
        Image $image,
        Config $catalogProductMediaConfig,
        ProductFactory $productFactory,
        DataInterfaceFactory $configFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        UiComponentFactory $uiComponentFactory,
        ContextFactory $contextFactory
    )
    {
        parent::__construct(
            $context,
            $image,
            $catalogProductMediaConfig,
            $productFactory
        );

        $this->uiComponentFactory = $uiComponentFactory;
        $this->configFactory = $configFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->contextFactory = $contextFactory;
    }

    /**
     * Generates configuration for JS
     *
     * @return array
     */
    private function generateJsonConfig(UiComponentInterface $data, string $provider)
    {
        $config = [];
        $children = $data->getChildComponents();
        $name = $data->getName();
        $data->prepare();
        $config[$name] = $data->getData();
        $config[$name]['name'] = $name;

        if (isset($config[$name]['config']['dataScope'])) {
            $config[$name]['dataScope'] = $config[$name]['config']['dataScope'];
        }

        if (!isset($config[$name]['provider'])) {
            $config[$name]['provider'] = $provider;
        }

        if (!empty($children)) {
            $config[$name]['children'] = $config[$name]['children'] ?? [];
            foreach ($children as $child) {
                $config[$name]['children'] = array_merge($config[$name]['children'], $this->generateJsonConfig($child, $provider));
            }
        }

        return $config;
    }

    /**
     * Composes configuration for JS
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $identifier = 'configurable_quantity_templates';
        $context = $this->contextFactory->create([
            'namespace' => $identifier
        ]);

        $component = $this->uiComponentFactory->create($identifier, null,  [
            'context' => $context
        ]);

        $data = $this->generateJsonConfig($component, $component->getConfiguration()['provider']);

        return $this->jsonSerializer->serialize($data);
    }
}
