<?php
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class Mapper
{
    /**
     * @var array
     */
    private $modules;

    /**
     * @var array
     */
    private $packageNameMap;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;
        $jsonDecoder = new \Magento\Framework\Json\Decoder();
        $readAdapter = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MODULES);
        foreach ($this->modules as $module) {
            $modulePartial = $this->moduleFullNameToModuleName($module);
            $vendor = $this->moduleFullNameToVendorName($module);
            $data = $jsonDecoder->decode($readAdapter->readFile("$vendor/$modulePartial/composer.json"));
            $this->packageNameMap[$data['name']] = $module;
        }
    }

    /**
     * Convert Magento_X to X
     *
     * @param string $moduleFullName
     * @return string
     */
    public function moduleFullNameToModuleName($moduleFullName)
    {
        return explode('_', $moduleFullName)[1];
    }

    /**
     * Convert Magento_X to Magento
     */
    public function moduleFullNameToVendorName($moduleFullName)
    {
        return explode('_', $moduleFullName)[0];
    }

    /**
     * Convert magento/modules-x to Magento_X
     *
     * @param string $packageName
     * @return string
     */
    public function packageNameToModuleFullName($packageName)
    {
        return isset($this->packageNameMap[$packageName]) ? $this->packageNameMap[$packageName] : '';
    }
}
