<?php
/**
 * Rule for searching php file dependency
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency\VirtualType;

use DOMDocument;
use Magento\Framework\App\Utility\Files;

class VirtualTypeMapper
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * @var string
     */
    private static $mainScope = 'global';

    /**
     * VirtualTypeMapper constructor.
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param string $name
     * @param string $scope
     * @return string|null
     */
    public function getType($name, $scope = null)
    {
        if (empty($this->map)) {
            $this->loadMap();
        }

        $scopes = [];
        if ($scope !== null && $scope !== self::$mainScope) {
            array_unshift($scopes, $scope);
        }
        $scopes[] = self::$mainScope;

        foreach ($scopes as $scp) {
            if (isset($this->map[$scp][$name])) {
                return $this->map[$scp][$name];
            }
        }

        return $name;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getScopeFromFile($file)
    {
        $basename = basename(pathinfo($file, PATHINFO_DIRNAME));
        return $basename === 'etc' ? 'global' : $basename;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function loadDiConfigs()
    {
        return Files::init()->getDiConfigs();
    }

    /**
     * @param array $diFiles
     * @return array
     * @throws \Exception
     */
    public function loadMap(array $diFiles = [])
    {
        if (empty($diFiles)) {
            $diFiles = $this->loadDiConfigs();
        }

        foreach ($diFiles as $file) {
            $scope = $this->getScopeFromFile($file);
            $doc = new DOMDocument();
            $doc->loadXML(file_get_contents($file));

            $nodes = $doc->getElementsByTagName('virtualType');
            /** @var \DOMElement $node */
            foreach ($nodes as $node) {
                $name = $node->getAttribute('name');
                $type = $node->getAttribute('type');
                if (empty($type)) {
                    continue;
                }
                $this->map[$scope][$name] = $type;
            }
        }

        return $this->map;
    }
}
