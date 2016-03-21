<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\Provider\Component;

use Magento\Framework\Phrase;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\Config\Converter;
use Magento\Framework\View\Element\UiComponent\ArrayObjectFactory;
use Magento\Framework\View\Element\UiComponent\Config\UiReaderInterface;

/**
 * Class Definition
 */
class Definition
{
    /**
     * ID in the storage cache
     */
    const CACHE_ID = 'ui_component_definition_data';

    /**
     * Components node name in config
     */
    const COMPONENTS_KEY = 'components';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * UI component data
     *
     * @var \ArrayObject
     */
    protected $componentData;

    /**
     * Constructor
     *
     * @param UiReaderInterface $uiReader
     * @param ArrayObjectFactory $arrayObjectFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        UiReaderInterface $uiReader,
        ArrayObjectFactory $arrayObjectFactory,
        CacheInterface $cache
    ) {
        $this->cache = $cache;
        $this->componentData = $arrayObjectFactory->create();
        $cachedData = $this->cache->load(static::CACHE_ID);
        if ($cachedData === false) {
            $data = $uiReader->read();
            $this->cache->save(serialize($data), static::CACHE_ID);
        } else {
            $data = unserialize($cachedData);
        }
        $this->prepareComponentData($data);
    }

    /**
     * Get component data
     *
     * @param string $name
     * @return array
     * @throws LocalizedException
     */
    public function getComponentData($name)
    {
        if (!$this->componentData->offsetExists($name)) {
            throw new LocalizedException(
                new Phrase(
                    'The requested component ("' . $name . '") is not found. '
                    . 'Before using, you must add the implementation.'
                )
            );
        }
        return (array) $this->componentData->offsetGet($name);
    }

    /**
     * Set component data
     *
     * @param string $name
     * @param array $data
     * @return void
     */
    public function setComponentData($name, array $data)
    {
        $this->componentData->offsetSet($name, $data);
    }

    /**
     * Prepare configuration data for the component
     *
     * @param array $componentsData
     * @return void
     */
    protected function prepareComponentData(array $componentsData)
    {
        $componentsData = reset($componentsData[static::COMPONENTS_KEY]);
        unset($componentsData[Converter::DATA_ATTRIBUTES_KEY]);
        foreach ($componentsData as $name => $data) {
            $this->setComponentData($name, reset($data));
        }
    }
}
