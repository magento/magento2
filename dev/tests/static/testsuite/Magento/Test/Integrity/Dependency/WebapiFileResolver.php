<?php

namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\Component\ComponentRegistrar;

class WebapiFileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var string[]
     */
    private $webapixml_paths;

    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @inheritDoc
     */
    public function get($filename, $scope)
    {
        if (!$this->webapixml_paths) {
            $paths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
            $webapixml_paths = [];
            foreach ($paths as $path) {
                $path = $path . '/etc/webapi.xml';
                if (file_exists($path)) {
                    $webapixml_paths[$path] = file_get_contents($path);
                }
            }
            $this->webapixml_paths = $webapixml_paths;
        }
        return $this->webapixml_paths;
    }
}
