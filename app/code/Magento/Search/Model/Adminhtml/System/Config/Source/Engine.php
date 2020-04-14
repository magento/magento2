<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Adminhtml\System\Config\Source;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * All registered search adapters
 *
 * @api
 * @since 100.0.2
 */
class Engine implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Engines list
     *
     * @var array
     */
    private $engines;

    /**
     * Engine Resolver
     *
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param array $engines
     * @param EngineResolverInterface|null $engineResolver
     */
    public function __construct(
        array $engines,
        EngineResolverInterface $engineResolver = null
    ) {
        $this->engines = $engines;
        $this->engineResolver = $engineResolver ?? ObjectManager::getInstance()->get(EngineResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [['value' => null, 'label' => __('--Please Select--')]];
        foreach ($this->engines as $key => $label) {
            if ($this->engineResolver->getCurrentSearchEngine() === $key) {
                $label = $label . ' ' . __('Default');
            }
            $options[] = ['value' => $key, 'label' => $label];
        }
        return $options;
    }
}
