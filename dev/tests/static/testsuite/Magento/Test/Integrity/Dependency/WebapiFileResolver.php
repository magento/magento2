<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Collects all webapi.xml files
 */
class WebapiFileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var string[]
     */
    private $webapiXmlPaths;

    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @inheritDoc
     */
    public function get($filename, $scope)
    {
        if (!$this->webapiXmlPaths) {
            $paths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
            $webapiXmlPaths = [];
            foreach ($paths as $path) {
                $path = $path . '/etc/webapi.xml';
                if (file_exists($path)) {
                    $webapiXmlPaths[$path] = file_get_contents($path);
                }
            }
            $this->webapiXmlPaths = $webapiXmlPaths;
        }
        return $this->webapiXmlPaths;
    }
}
