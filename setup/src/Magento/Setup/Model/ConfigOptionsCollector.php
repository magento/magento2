<?php
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

/**
 * Collects all ConfigOptions throughout modules, framework and setup
 */
class ConfigOptionsCollector
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Module list including enabled and disabled modules
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * Enabled module list
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param FullModuleList $fullModuleList
     * @param ModuleList $moduleList
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        FullModuleList $fullModuleList,
        ModuleList $moduleList,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Auto discover Options class and collect their options
     *
     * @return array
     */
    public function collectOptions()
    {
        $optionsList = [];

        // go through modules
        foreach ($this->moduleList->getNames() as $moduleName) {
            $optionsClassName = str_replace('_', '\\', $moduleName) . '\Setup\ConfigOptions';
            if (class_exists($optionsClassName)) {
                $optionsClass = $this->objectManagerProvider->get()->create($optionsClassName);
                if ($optionsClass instanceof \Magento\Framework\Setup\ConfigOptionsInterface) {
                    $optionsList[$optionsClassName] = [
                        'options' => $optionsClass->getOptions(),
                        'enabled' => $this->moduleList->has($moduleName),
                    ];
                }
            }
        }

        // go through framework
        $frameworkOptionsFiles = [];
        $this->collectRecursively(
            $this->filesystem->getDirectoryRead(DirectoryList::LIB_INTERNAL),
            'Magento/Framework',
            $frameworkOptionsFiles
        );
        foreach ($frameworkOptionsFiles as $frameworkOptionsFile) {
            // remove .php
            $frameworkOptionsFile = substr($frameworkOptionsFile, 0, -4);
            $frameworkOptionsClassName = str_replace('/', '\\', $frameworkOptionsFile);
            $optionsClass = $this->objectManagerProvider->get()->create($frameworkOptionsClassName);
            if ($optionsClass instanceof \Magento\Framework\Setup\ConfigOptionsInterface) {
                $optionsList[$frameworkOptionsClassName] = [
                    'options' => $optionsClass->getOptions(),
                    'enabled' => true,
                ];
            }
        }

        // go through setup
        $setupOptionsFiles = [];
        $this->collectRecursively(
            $this->filesystem->getDirectoryRead(DirectoryList::ROOT),
            'setup/src',
            $setupOptionsFiles
        );
        foreach ($setupOptionsFiles as $setupOptionsFile) {
            // remove setup/src/ and .php
            $setupOptionsFile = substr($setupOptionsFile, 10, -4);
            $setupOptionsClassName = str_replace('/', '\\', $setupOptionsFile);
            $optionsClass = $this->objectManagerProvider->get()->create($setupOptionsClassName);
            if ($optionsClass instanceof \Magento\Framework\Setup\ConfigOptionsInterface) {
                $optionsList[$setupOptionsClassName] = [
                    'options' => $optionsClass->getOptions(),
                    'enabled' => true,
                ];
            }
        }

        return $optionsList;
    }

    /**
     * Collects Options files recursively
     *
     * @param Filesystem\Directory\ReadInterface $dir
     * @param string $path
     * @param array $result
     */
    private function collectRecursively(\Magento\Framework\Filesystem\Directory\ReadInterface $dir, $path, &$result)
    {
        $localResult = $dir->search($path . '/Setup/ConfigOptions.php');
        foreach ($localResult as $optionFile) {
            $result[] = $optionFile;
        }

        // goes deeper if current search is successful or next depth level exists
        if ($localResult || $dir->search($path . '/*')) {
            $this->collectRecursively($dir, $path . '/*', $result);
        }
    }
}
