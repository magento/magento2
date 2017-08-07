<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\VclGeneratorInterfaceFactory;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Varnish\VclTemplateLocator;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class GenerateVclCommand extends Command
{
    /**
     * Access list option name
     */
    const ACCESS_LIST_OPTION = 'access-list';

    /**
     * Backend host option name
     */
    const BACKEND_HOST_OPTION = 'backend-host';

    /**
     * Backend port option name
     */
    const BACKEND_PORT_OPTION = 'backend-port';

    /**
     * Varnish version option name
     */
    const EXPORT_VERSION_OPTION = 'export-version';

    /**
     * Grace period option name
     */
    const GRACE_PERIOD_OPTION = 'grace-period';

    /**
     * Output file option name
     */
    const OUTPUT_FILE_OPTION = 'output-file';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory
     * @since 2.2.0
     */
    private $writeFactory;

    /**
     * @var VclGeneratorInterfaceFactory
     * @since 2.2.0
     */
    private $vclGeneratorFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $inputToVclMap = [
        self::ACCESS_LIST_OPTION => 'accessList',
        self::BACKEND_PORT_OPTION => 'backendPort',
        self::BACKEND_HOST_OPTION => 'backendHost',
        self::GRACE_PERIOD_OPTION => 'gracePeriod',
    ];

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function configure()
    {
        $this->setName('varnish:vcl:generate')
            ->setDescription('Generates Varnish VCL and echos it to the command line')
            ->setDefinition($this->getOptionList());
    }

    /**
     * @param VclGeneratorInterfaceFactory $vclGeneratorFactory
     * @param WriteFactory $writeFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     * @since 2.2.0
     */
    public function __construct(
        VclGeneratorInterfaceFactory $vclGeneratorFactory,
        WriteFactory $writeFactory,
        ScopeConfigInterface $scopeConfig,
        Json $serializer
    ) {
        parent::__construct();
        $this->writeFactory = $writeFactory;
        $this->vclGeneratorFactory = $vclGeneratorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            foreach ($errors as $error) {
                $output->writeln('<error>'.$error.'</error>');

                return Cli::RETURN_FAILURE;
            }
        }

        try {
            $outputFile = $input->getOption(self::OUTPUT_FILE_OPTION);
            $varnishVersion = $input->getOption(self::EXPORT_VERSION_OPTION);
            $vclParameters = array_merge($this->inputToVclParameters($input), [
                'sslOffloadedHeader' => $this->getSslOffloadedHeader(),
                'designExceptions' => $this->getDesignExceptions(),
            ]);
            $vclGenerator = $this->vclGeneratorFactory->create($vclParameters);
            $vcl = $vclGenerator->generateVcl($varnishVersion);

            if ($outputFile) {
                $writer = $this->writeFactory->create($outputFile, DriverPool::FILE, 'w+');
                $writer->write($vcl);
                $writer->close();
            } else {
                $output->writeln($vcl);
            }

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Get list of options for the command
     *
     * @return InputOption[]
     * @since 2.2.0
     */
    private function getOptionList()
    {
        return [
            new InputOption(
                self::ACCESS_LIST_OPTION,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'IPs access list that can purge Varnish',
                ['localhost']
            ),
            new InputOption(
                self::BACKEND_HOST_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Host of the web backend',
                'localhost'
            ),
            new InputOption(
                self::BACKEND_PORT_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Port of the web backend',
                8080
            ),
            new InputOption(
                self::EXPORT_VERSION_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'The version of Varnish file',
                VclTemplateLocator::VARNISH_SUPPORTED_VERSION_4
            ),
            new InputOption(
                self::GRACE_PERIOD_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Grace period in seconds',
                300
            ),
            new InputOption(
                self::OUTPUT_FILE_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the file to write vcl'
            ),
        ];
    }

    /**
     * @param InputInterface $input
     * @return array
     * @since 2.2.0
     */
    private function inputToVclParameters(InputInterface $input)
    {
        $parameters = [];

        foreach ($this->inputToVclMap as $inputKey => $vclKey) {
            $parameters[$vclKey] = $input->getOption($inputKey);
        }

        return $parameters;
    }

    /**
     * Input validation
     *
     * @param InputInterface $input
     * @return array
     * @since 2.2.0
     */
    private function validate(InputInterface $input)
    {
        $errors = [];

        if ($input->hasOption(self::BACKEND_PORT_OPTION)
            && ($input->getOption(self::BACKEND_PORT_OPTION) < 0
                || $input->getOption(self::BACKEND_PORT_OPTION) > 65535)
        ) {
            $errors[] = 'Invalid backend port value';
        }

        if ($input->hasOption(self::GRACE_PERIOD_OPTION)
            && $input->getOption(self::GRACE_PERIOD_OPTION) < 0
        ) {
            $errors[] = 'Grace period can\'t be lower than 0';
        }

        return $errors;
    }

    /**
     * Get ssl Offloaded header
     *
     * @return mixed
     * @since 2.2.0
     */
    private function getSslOffloadedHeader()
    {
        return $this->scopeConfig->getValue(Request::XML_PATH_OFFLOADER_HEADER);
    }

    /**
     * Get design exceptions
     *
     * @return array
     * @since 2.2.0
     */
    private function getDesignExceptions()
    {
        $expressions = $this->scopeConfig->getValue(
            Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
            ScopeInterface::SCOPE_STORE
        );

        return $expressions ? $this->serializer->unserialize($expressions) : [];
    }
}
