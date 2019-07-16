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

use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Downloadable\Model\Url\DomainValidator;

/**
 * Class DomainsAddCommand
 *
 * Command for listing allowed downloadable domains
 */
class DomainsShowCommand extends Command
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * DomainsShowCommand constructor.
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
        $description = 'Display downloadable domains whitelist';

        $this->setName('downloadable:domains:show')
            ->setDescription($description);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $whitelist = implode("\n", $this->domainValidator->getEnvDomainWhitelist() ?: []);
            $output->writeln(
                "Downloadable domains whitelist:\n$whitelist"
            );

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return;
        }
    }
}
