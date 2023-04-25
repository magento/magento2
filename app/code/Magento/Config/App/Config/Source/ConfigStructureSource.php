<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\App\Config\Source;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;

/**
 * Class for retrieving configuration structure
 */
class ConfigStructureSource implements ConfigSourceInterface
{
    /**
     * @var Structure
     */
    private $structure;

    /**
     * @param Structure $structure
     */
    public function __construct(Structure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $fieldPaths = array_keys($this->structure->getFieldPaths());
        $defaultConfig = [];
        foreach ($fieldPaths as $fieldPath) {
            $defaultConfig = $this->addPathToConfig($defaultConfig, $fieldPath);
        }
        $data = new DataObject(['default' => $defaultConfig]);

        return $data->getData($path);
    }

    /**
     * Add config path to config structure
     *
     * @param array $config
     * @param string $path
     * @return array
     */
    private function addPathToConfig(array $config, string $path): array
    {
        if (strpos($path, '/') !== false) {
            list ($key, $subPath) = explode('/', $path, 2);
            $config[$key] = $this->addPathToConfig(
                $config[$key] ?? [],
                $subPath
            );
        } else {
            $config[$path] = null;
        }

        return $config;
    }
}
