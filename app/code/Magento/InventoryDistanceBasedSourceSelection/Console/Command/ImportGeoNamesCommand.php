<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Console\Command;

use Magento\InventoryDistanceBasedSourceSelection\Model\ImportGeoNames;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import geo names from geonames.org
 *
 * {@inheritdoc}
 */
class ImportGeoNamesCommand extends Command
{
    private const COUNTRIES = 'countries'; // Parameter name for countries list

    /**
     * @var ImportGeoNames
     */
    private $importGeoNames;

    /**
     * ImportGeoNamesCommand constructor.
     *
     * @param ImportGeoNames $importGeoNames
     * @param null|string $name
     */
    public function __construct(
        ImportGeoNames $importGeoNames,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->importGeoNames = $importGeoNames;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('inventory-geonames:import')
            ->setDescription('Download and import geo names for source selection algorithm')
            ->setDefinition([
                new InputArgument(
                    self::COUNTRIES,
                    InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                    'List of country codes to import'
                )
            ]);

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $countries = $input->getArgument(self::COUNTRIES);

        foreach ($countries as $country) {
            $output->write('Importing ' . $country . ': ');
            try {
                $this->importGeoNames->execute($country);
                $output->writeln('OK');
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln('Done.');
    }
}
