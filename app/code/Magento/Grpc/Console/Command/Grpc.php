<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Grpc\Console\Command;

use Composer\Config as ComposerConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Module\ModuleList;

/**
 * Command for grpc server and grpc_services_map initialization
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grpc extends Command
{
    /**
     * Command name
     * @var string
     */
    private const COMMAND_NAME = 'storefront:grpc:init';

    /**
     * Argument name for services that be used into gRPC server
     *
     * @var string
     */
    private const INPUT_SERVICE = 'service';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var Dir
     */
    private $dir;

    /**
     * @var string
     */
    private $moduleName = 'Magento_Grpc';

    /**
     * @var ComposerConfig
     */
    private $composerConfig;

    /**
     * @var string[]
     */
    private $filesCopyToVendorBin = [
        'grpc-server',
        'grpc-workers',
        'worker',
    ];

    /**
     * @param Filesystem $fileSystem
     * @param ModuleList $moduleList
     * @param Dir $dir
     * @param ComposerConfig $composerConfig
     */
    public function __construct(
        Filesystem $fileSystem,
        ModuleList $moduleList,
        Dir $dir,
        ComposerConfig $composerConfig
    ) {
        parent::__construct();
        $this->fileSystem = $fileSystem;
        $this->moduleList = $moduleList;
        $this->dir = $dir;
        $this->composerConfig = $composerConfig;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $inputList = [
            new InputArgument(
                self::INPUT_SERVICE,
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Services to be used into gRPC server.'
            )
        ];
        $this->setName(self::COMMAND_NAME)->setDescription(
            'Initializes gRPC server and services map'
        )->setDefinition($inputList);

        parent::configure();
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = $input->getArgument(self::INPUT_SERVICE);

        /** @var WriteInterface $directoryWrite */
        $directoryWrite = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);
        $moduleRoot = $this->dir->getDir($this->moduleName);
        /** @var DriverInterface $writeDriver */
        $writeDriver = $directoryWrite->getDriver();

        $this->copyToVendorBin($writeDriver, $output, $moduleRoot);
        $this->createDefaultServiceMap($writeDriver, $output, $services);

        return 0;
    }

    /**
     * Copies files to vendor/bin folder
     *
     * @param DriverInterface $writeDriver
     * @param OutputInterface $output
     * @param string $moduleRoot
     * @throws FileSystemException
     */
    private function copyToVendorBin(
        DriverInterface $writeDriver,
        OutputInterface $output,
        string $moduleRoot
    ): void {
        $vendorFolder = $this->composerConfig->get('vendor-dir');

        foreach ($this->filesCopyToVendorBin as $filename) {
            $moduleBinPath = $moduleRoot . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $filename;
            $vendorBinPath = BP . $vendorFolder
                . DIRECTORY_SEPARATOR . 'bin'
                . DIRECTORY_SEPARATOR . $filename;
            $this->copyFileToVendorBin($writeDriver, $output, $moduleBinPath, $vendorBinPath);
        }
    }

    /**
     * Copy bin file from module to vendor.
     *
     * @param DriverInterface $writeDriver
     * @param OutputInterface $output
     * @param string $source
     * @param string $destination
     * @throws FileSystemException
     */
    private function copyFileToVendorBin(
        DriverInterface $writeDriver,
        OutputInterface $output,
        string $source,
        string $destination
    ) {
        if (!$writeDriver->isExists($destination) && $writeDriver->isExists($source)) {
            if (!$writeDriver->isExists($writeDriver->getParentDirectory($destination))) {
                $writeDriver->createDirectory($writeDriver->getParentDirectory($destination));
            }
            $writeDriver->copy($source, $destination);
            $writeDriver->changePermissions($destination, 0555);
            $output->writeln(
                \sprintf('<info>"%s" successfully copied to bin folder</info>', $destination)
            );
        } else {
            $output->writeln(
                \sprintf('<info>"%s" already exists</info>', $destination)
            );
        }
    }

    /**
     * Create default service map.
     *
     * @param Filesystem\DriverInterface $writeDriver
     * @param OutputInterface $output
     * @param array $services
     * @throws FileSystemException
     */
    private function createDefaultServiceMap(
        DriverInterface $writeDriver,
        OutputInterface $output,
        array $services
    ): void {
        $servicesFile = BP . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR .
            'code' . DIRECTORY_SEPARATOR . 'grpc_services_map.php';
        $serviceClasses = $this->prepareServiceClasses($services);

        $content =
            <<<SERVICE
<?php
return [
    $serviceClasses
];

SERVICE;
        $writeDriver->touch($servicesFile);
        $resource = $writeDriver->fileOpen($servicesFile, 'wb');
        $writeDriver->fileWrite($resource, $content);
        $writeDriver->fileClose($resource);
        $output->writeln(
            \sprintf('<info>Services map is dumped in "%s"</info>', $servicesFile)
        );
    }

    /**
     * Prepares a list of service classes.
     *
     * @param array $services
     * @return string
     */
    private function prepareServiceClasses(array $services): string
    {
        $classesList = [];
        foreach ($services as $service) {
            if (true === $this->validateService($service)) {
                $classesList[] = \sprintf('%s::class', $service);
            }
        }

        return \implode(",\n\t", $classesList);
    }

    /**
     * Validate service
     *
     * @param string $service
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    private function validateService(string $service): bool
    {
        if (!\class_exists($service)) {
            throw new \InvalidArgumentException(
                \sprintf('Service class "%s" was not found', $service)
            );
        }

        if (!\is_subclass_of($service, \Spiral\GRPC\ServiceInterface::class)) {
            throw new \InvalidArgumentException(
                \sprintf('Service class "%s" must implement \Spiral\GRPC\ServiceInterface', $service)
            );
        }

        return true;
    }
}
