<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\App\Config\Source;

use Magento\Config\Model\Config\Structure\Reader;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;

/**
 * Class for retrieving configuration structure
 */
class ConfigStructureSource implements ConfigSourceInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $configStructure = $this->reader->read(Area::AREA_ADMINHTML);
        $sections = $configStructure['config']['system']['sections'] ?? [];
        $defaultConfig = $this->merge([], $sections);
        $data = new DataObject(['default' => $defaultConfig]);

        return $data->getData($path);
    }

    /**
     * Merge existed config with config structure
     *
     * @param array $config
     * @param array $sections
     * @return array
     */
    private function merge(array $config, array $sections): array
    {
        foreach ($sections as $section) {
            if (isset($section['children'])) {
                $config[$section['id']] = $this->merge(
                    $config[$section['id']] ?? [],
                    $section['children']
                );
            } elseif ($section['_elementType'] === 'field') {
                $config += [$section['id'] => null];
            }
        }

        return $config;
    }
}
