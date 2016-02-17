<?php
/**
 *
 *
 *
 *
 */

namespace MagentoHackathon\Composer\Magento;

use Composer\Autoload\AutoloadGenerator;
use Composer\Autoload\ClassMapGenerator;
use Composer\EventDispatcher\EventDispatcher;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginEvents;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Symfony\Component\Process\Process;

class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var IOInterface
     */
    protected $io;


    /**
     * @var ProjectConfig
     */
    protected $config;


    /**
     * @var DeployManager
     */
    protected $deployManager;


    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function initDeployManager(Composer $composer, IOInterface $io)
    {
        $this->deployManager = new DeployManager($io);

        $extra = $composer->getPackage()->getExtra();
        $sortPriority = isset($extra['magento-deploy-sort-priority']) ? $extra['magento-deploy-sort-priority'] : array();
        $this->deployManager->setSortPriority($sortPriority);

    }


    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->filesystem = new Filesystem();
        $this->config = new ProjectConfig($composer->getPackage()->getExtra());
        $this->installer = new Installer($io, $composer);
        $this->initDeployManager($composer, $io);
        $this->installer->setDeployManager($this->deployManager);
        $this->installer->setConfig($this->config);
        if ($this->io->isDebug()) {
            $this->io->write('activate magento plugin');
        }
        $composer->getInstallationManager()->addInstaller($this->installer);
    }

    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::COMMAND => array(
                array('onCommandEvent', 0),
            ),
            ScriptEvents::POST_INSTALL_CMD => array(
                array('onNewCodeEvent', 0),
            ),
            ScriptEvents::POST_UPDATE_CMD => array(
                array('onNewCodeEvent', 0),
            ),
            ScriptEvents::POST_PACKAGE_UNINSTALL => array(
                array('onPackageUnistall', 0),
            )
        );
    }

    public function onPackageUnistall(\Composer\Script\PackageEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $package = $event->getOperation()->getPackage();
        list($vendor, $packageName) = explode('/', $package->getPrettyName());
        $packageName = trim(str_replace('module-', '', $packageName));
        $packageInstallationPath = $packageInstallationPath = $this->installer->getTargetDir();
        $packagePath = ucfirst($vendor) . $ds . str_replace(' ', '', ucwords(str_replace('-', ' ', $packageName)));
        $this->io->write("Removing $packagePath");
        $libPath = 'lib' . $ds . 'internal' . $ds . $packagePath;
        $magentoPackagePath = 'app' . $ds . 'code' . $ds . $packagePath;
        $deployStrategy = $this->installer->getDeployStrategy($package);
        $deployStrategy->rmdirRecursive($packageInstallationPath . $ds . $libPath);
        $deployStrategy->rmdirRecursive($packageInstallationPath . $ds . $magentoPackagePath);
    }

    /**
     * actually is triggered before anything got executed
     *
     * @param \Composer\Plugin\CommandEvent $event
     */
    public function onCommandEvent(\Composer\Plugin\CommandEvent $event)
    {
        $command = $event->getCommandName();
    }

    /**
     * event listener is named this way, as it listens for events leading to changed code files
     *
     * @param \Composer\Script\CommandEvent $event
     */
    public function onNewCodeEvent(\Composer\Script\CommandEvent $event)
    {
        if ($this->io->isDebug()) {
            $this->io->write('start magento deploy via deployManager');
        }

        $this->deployManager->doDeploy();
        $this->deployLibraries();
        $this->saveVendorDirPath($event->getComposer());
    }


    protected function deployLibraries()
    {
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $autoloadDirectories = array();

        $libraryPath = $this->config->getLibraryPath();
        if ($libraryPath === null) {
            if ($this->io->isDebug()) {
                $this->io->write('jump over deployLibraries as no Magento libraryPath is set');
            }
            return;
        }


        $vendorDir = rtrim($this->composer->getConfig()->get('vendor-dir'), '/');

        $filesystem = $this->filesystem;
        $filesystem->removeDirectory($libraryPath);
        $filesystem->ensureDirectoryExists($libraryPath);

        foreach ($packages as $package) {
            /** @var PackageInterface $package */
            $packageConfig = $this->config->getLibraryConfigByPackagename($package->getName());
            if ($packageConfig === null) {
                continue;
            }
            if (!isset($packageConfig['autoload'])) {
                $packageConfig['autoload'] = array('/');
            }
            foreach ($packageConfig['autoload'] as $path) {
                $autoloadDirectories[] = $libraryPath . '/' . $package->getName() . "/" . $path;
            }
            if ($this->io->isDebug()) {
                $this->io->write('Magento deployLibraries executed for ' . $package->getName());
            }
            $libraryTargetPath = $libraryPath . '/' . $package->getName();
            $filesystem->removeDirectory($libraryTargetPath);
            $filesystem->ensureDirectoryExists($libraryTargetPath);
            $this->copyRecursive($vendorDir . '/' . $package->getPrettyName(), $libraryTargetPath);

        }

        $autoloadGenerator = new AutoloadGenerator(new EventDispatcher($this->composer, $this->io));
        $classmap = ClassMapGenerator::createMap($libraryPath);
        $executable = $this->composer->getConfig()->get('bin-dir') . '/phpab';
        if (!file_exists($executable)) {
            $executable = $this->composer->getConfig()->get('vendor-dir') . '/theseer/autoload/composer/bin/phpab';
        }
        if (file_exists($executable)) {
            if ($this->io->isDebug()) {
                $this->io->write('Magento deployLibraries executes autoload generator');
            }
            $process = new Process($executable . " -o {$libraryPath}/autoload.php  " . implode(' ', $autoloadDirectories));
            $process->run();
        } else {
            if ($this->io->isDebug()) {
                $this->io->write('Magento deployLibraries autoload generator not availabel, you should require "theseer/autoload"');
                var_dump($executable, getcwd());

            }
        }


    }


    /**
     * Copy then delete is a non-atomic version of {@link rename}.
     *
     * Some systems can't rename and also don't have proc_open,
     * which requires this solution.
     *
     * copied from \Composer\Util\Filesystem::copyThenRemove and removed the remove part
     *
     * @param string $source
     * @param string $target
     */
    protected function copyRecursive($source, $target)
    {
        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        $this->filesystem->ensureDirectoryExists($target);

        foreach ($ri as $file) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
            if ($file->isDir()) {
                $this->filesystem->ensureDirectoryExists($targetPath);
            } else {
                copy($file->getPathname(), $targetPath);
            }
        }

    }

    /**
     * Generate file with path to Composer 'vendor' dir to be used by the application
     *
     * @param \Composer\Composer $composer
     * @throws \UnexpectedValueException
     */
    private function saveVendorDirPath(Composer $composer)
    {
        $magentoDir = $this->installer->getTargetDir();
        $vendorDirPath = $this->filesystem->findShortestPath(
            $magentoDir,
            realpath($composer->getConfig()->get('vendor-dir')),
            true
        );
        $vendorPathFile = $magentoDir . '/app/etc/vendor_path.php';
        $content = <<<AUTOLOAD
<?php
/**
 * Path to Composer vendor directory
 */
return '$vendorDirPath';

AUTOLOAD;
        file_put_contents($vendorPathFile, $content);
    }
}
