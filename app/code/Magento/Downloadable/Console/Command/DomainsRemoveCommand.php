<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Console\Command;

use Exception;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    public const INPUT_KEY_DOMAINS = 'domains';

    /**
     * @var DomainManager
     */
    private $domainManager;

    /**
     * DomainsRemoveCommand constructor.
     *
     * @param DomainManager $domainManager
     */
    public function __construct(
        DomainManager $domainManager
    ) {
        $this->domainManager = $domainManager;
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
            $domains = $input->getArgument(self::INPUT_KEY_DOMAINS);

            $this->validateDomains($domains);

            $whitelistBefore = $this->domainManager->getDomains();
            $removedDomains = array_filter(array_map('trim', $domains), 'strlen');

            $this->domainManager->removeDomains($removedDomains);

            foreach (array_intersect($removedDomains, $whitelistBefore) as $removedHost) {
                $output->writeln($removedHost . ' was removed from the whitelist.' . PHP_EOL);
            }

            return Cli::RETURN_SUCCESS;
        } catch (\InvalidArgumentException $e) {
            return $this->handleInvalidArgumentException($e, $output);
        } catch (Exception $e) {
            return $this->handleException($e, $output);
        }
    }

    /**
     * Validate the input domains array
     *
     * @param array $domains
     * @return void
     */
    protected function validateDomains(array $domains)
    {
        if (empty($domains)) {
            throw new \InvalidArgumentException('Error: Domains parameter is missing.');
        }
    }

    /**
     * Handle the \InvalidArgumentException exception.
     *
     * @param \InvalidArgumentException $e
     * @param OutputInterface $output
     * @return int
     */
    protected function handleInvalidArgumentException(\InvalidArgumentException $e, OutputInterface $output): int
    {
        $output->writeln('<error>' . $e->getMessage() . '</error>');
        return Cli::RETURN_FAILURE;
    }

    /**
     * Handle any other exception thrown during command execution
     * @param Exception $e
     * @param OutputInterface $output
     * @return int
     */
  protected function handleException(Exception $e, OutputInterface $output): int
  {
      $output->writeln('<error>' . $e->getMessage() . '</error>');
      if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
          $output->writeln($e->getTraceAsString());
      }
      return Cli::RETURN_FAILURE;
  }
}
