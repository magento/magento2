<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Class DomainsAddCommand
 *
 * Command for adding downloadable domain to the whitelist
 */
class DomainsAddCommand extends Command
{
    /**
     * Name of domains input argument
     */
    public const INPUT_KEY_DOMAINS = 'domains';

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * DomainsAddCommand constructor.
     *
     * @param ConfigWriter $configWriter
     * @param DomainValidator $domainValidator
     */
    public function __construct(
        ConfigWriter $configWriter,
        DomainValidator $domainValidator
    ) {
        $this->configWriter = $configWriter;
        $this->domainValidator = $domainValidator;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $description = 'Add domains to the downloadable domains whitelist';

        $this->setName('downloadable:domains:add')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::INPUT_KEY_DOMAINS,
                        InputArgument::IS_ARRAY,
                        'Domains name'
                    )
                ]
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($input->getArgument(self::INPUT_KEY_DOMAINS)) {
                $newDomains = $input->getArgument(self::INPUT_KEY_DOMAINS);
                $newDomains = array_filter(array_map('trim', $newDomains), 'strlen');

                $whitelist = $this->domainValidator->getEnvDomainWhitelist() ?: [];
                foreach ($newDomains as $newDomain) {
                    if (in_array($newDomain, $whitelist)) {
                        $output->writeln(
                            "$newDomain is already in the whitelist"
                        );
                        continue;
                    } else {
                        array_push($whitelist, $newDomain);
                        $output->writeln(
                            "$newDomain was added to the whitelist"
                        );
                    }
                }

                $this->configWriter->saveConfig(
                    [
                        ConfigFilePool::APP_ENV => [
                            $this->domainValidator::PARAM_DOWNLOADABLE_DOMAINS => $whitelist
                        ]
                    ]
                );
            }

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return;
        }
    }
}
