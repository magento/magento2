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
 * Class DomainsRemoveCommand
 *
 * Command for removing downloadable domain from the whitelist
 */
class DomainsRemoveCommand extends Command
{
    /**
     * Name of domains input argument
     */
    const INPUT_KEY_DOMAINS = 'domains';

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * DomainsRemoveCommand constructor.
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
        $description = 'Remove domains from the downloadable domains whitelist';

        $this->setName('downloadable:domains:remove')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::INPUT_KEY_DOMAINS,
                        InputArgument::IS_ARRAY,
                        'Domain names'
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
                $removeDomains = $input->getArgument(self::INPUT_KEY_DOMAINS);
                $removeDomains = array_filter(array_map('trim', $removeDomains), 'strlen');

                $whitelist = $this->domainValidator->getEnvDomainWhitelist() ?: [];
                foreach ($removeDomains as $removeDomain) {
                    if (in_array($removeDomain, $whitelist)) {
                        $index = array_search($removeDomain, $whitelist);
                        unset($whitelist[$index]);
                        $output->writeln(
                            "$removeDomain was removed from the whitelist"
                        );
                        continue;
                    } else {
                        $output->writeln(
                            "$removeDomain is absent in the whitelist"
                        );
                    }
                }

                $this->configWriter->saveConfig(
                    [
                        ConfigFilePool::APP_ENV => [
                            $this->domainValidator::PARAM_DOWNLOADABLE_DOMAINS => $whitelist
                        ]
                    ],
                    true
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
