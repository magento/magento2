<?php
/**
 * Rule for searching php file dependency
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency\VirtualType;

use DOMDocument;
use Magento\Framework\App\Utility\Files;

class Mapper
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * @var string
     */
    private static $mainScope = 'etc';

    /**
     * @param string $name
     * @param string $scope
     * @return string|bool
     */
    public function getType($name, $scope = null)
    {
        if (empty($this->map)) {
            $this->load();
        }

        $scopes = [self::$mainScope];
        if ($scope !== null && $scope !== self::$mainScope) {
            array_unshift($scopes, $scope);
        }

        foreach ($scopes as $scope) {
            if (isset($this->map[$scope][$name])) {
                return $this->map[$scope][$name];
            }
        }
        return false;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getScopeFromFile($file)
    {
        return basename(pathinfo($file, PATHINFO_DIRNAME));
    }

    /**
     *
     * @throws \Exception
     */
    private function load()
    {
        $diFiles = Files::init()->getDiConfigs();
        foreach ($diFiles as $file) {
            $scope = $this->getScopeFromFile($file);
            $doc = new DOMDocument();
            $doc->loadXML(file_get_contents($file));

            $nodes = $doc->getElementsByTagName('virtualType');
            /** @var \DOMElement $node */
            foreach ($nodes as $node) {
                $name = $node->getAttribute('name');
                $type = $node->getAttribute('type');
                if (empty($type) || strpos($name, 'Magento\\') === 0) {
                    continue;
                }
                $this->map[$scope][$name] = $type;
            }
        }
    }
}
