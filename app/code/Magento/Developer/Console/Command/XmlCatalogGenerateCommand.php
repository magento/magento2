<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Dom\UrnResolver;

class XmlCatalogGenerateCommand extends Command
{
    /**
     * Option for the type of IDE
     */
    const IDE_OPTION = 'ide';

    /**
     * Argument for the path to IDE config file
     */
    const IDE_FILE_PATH_ARGUMENT = 'path';

    /**
     * @var Files
     */
    private $filesUtility;

    /**
     * @var UrnResolver
     */
    private $urnResolver;

    /**
     * @param Files $filesUtility
     * @param UrnResolver $urnResolver
     */
    public function __construct(
        Files $filesUtility,
        UrnResolver $urnResolver
    ) {
        $this->filesUtility = $filesUtility;
        $this->urnResolver = $urnResolver;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dev:xml-catalog:generate')
            ->setDescription('Collects URNs used in XML to reference XSD schemas. Generates catalog for IDE.')
            ->setDefinition([
                new InputOption(
                    self::IDE_OPTION,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'IDE for which catalog will be generated: [PhpStorm]',
                    'PhpStorm'
                ),
                new InputArgument(
                    self::IDE_FILE_PATH_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Path to the IDE config file. For PhpStorm it is .idea/misc.xml'
                )
            ]);

        parent::configure();
    }

    /**
     * Get an arry of URNs
     *
     * @return array
     */
    private function getUrnDictionary()
    {
        $files = $this->filesUtility->getXmlCatalogFiles('*.xml');
        $files = array_merge($files, $this->filesUtility->getXmlCatalogFiles('*.xsd'));

        $urns = [];
        foreach ($files as $file) {
            $content = file_get_contents($file[0]);
            $matches = [];
            preg_match_all('/schemaLocation="(urn\:[^"]*)"/i', $content, $matches);
            $urns = array_merge($urns, $matches[1]);
        }
        $urns = array_unique($urns);

        $paths = [];
        foreach ($urns as $urn) {
            $paths[$urn] = $this->urnResolver->getRealPath($urn);
        }
        return $paths;
    }

    /**
     * Format URN dictionary for PhpStorm
     *
     * @param array $dictionary
     * @param string $ideFilePath
     * @return null
     */
    private function formatForPhpStorm($dictionary, $ideFilePath)
    {
        $componentNode = null;
        $projectNode = null;
        if (file_exists($ideFilePath)) {
            $dom = new \DOMDocument();
            $dom->load($ideFilePath);
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/project');
            $projectNode = $nodeList->item(0);
        } else {
            $dom = new \DOMDocument();
            $projectNode = $dom->createElement('project');
            $projectNode->setAttribute('version', '4');
            $dom->appendChild($projectNode);
            $rootComponentNode = $dom->createElement('component');
            $rootComponentNode->setAttribute('version', '2');
            $rootComponentNode->setAttribute('name', 'ProjectRootManager');
            $projectNode->appendChild($rootComponentNode);

        }

        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->query("/project/component[@name='ProjectResources']");
        $componentNode = $nodeList->item(0);
        if ($componentNode == null) {
            $componentNode = $dom->createElement('component');
            $componentNode->setAttribute('name', 'ProjectResources');
            $projectNode->appendChild($componentNode);
        }

        foreach ($dictionary as $urn => $path) {
            $node = $dom->createElement('resource');
            $node->setAttribute('url', $urn);
            $node->setAttribute('location', $path);
            $componentNode->appendChild($node);
        }
        $dom->formatOutput = true;
        file_put_contents($ideFilePath, $dom->saveXML(), FILE_TEXT);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ideName = $input->getOption(self::IDE_OPTION);
        $ideFilePath = $input->getArgument(self::IDE_FILE_PATH_ARGUMENT);
        $urnDictionary = $this->getUrnDictionary();
        switch ($ideName) {
            default:
            case 'PhpStorm':
                $this->formatForPhpStorm($urnDictionary, $ideFilePath);
                break;
        }
    }


}