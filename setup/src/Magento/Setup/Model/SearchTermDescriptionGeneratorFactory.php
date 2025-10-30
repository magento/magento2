<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Fixtures\FixtureConfig;
use Magento\Setup\Model\Description\DescriptionSentenceGeneratorFactory;
use Magento\Setup\Model\Description\DescriptionParagraphGeneratorFactory;
use Magento\Setup\Model\Description\DescriptionGeneratorFactory;
use Magento\Setup\Model\DictionaryFactory;
use Magento\Setup\Model\SearchTermManagerFactory;

/**
 * Search term description generator factory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchTermDescriptionGeneratorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Fixtures\FixtureConfig
     */
    private $fixtureConfig;

    /**
     * @var \Magento\Setup\Model\Description\DescriptionSentenceGeneratorFactory
     */
    private $sentenceGeneratorFactory;

    /**
     * @var \Magento\Setup\Model\Description\DescriptionParagraphGeneratorFactory
     */
    private $paragraphGeneratorFactory;

    /**
     * @var \Magento\Setup\Model\Description\DescriptionGeneratorFactory
     */
    private $descriptionGeneratorFactory;

    /**
     * @var \Magento\Setup\Model\DictionaryFactory
     */
    private $dictionaryFactory;

    /**
     * @var \Magento\Setup\Model\SearchTermManagerFactory
     */
    private $searchTermManagerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param FixtureConfig $fixtureConfig
     * @param DescriptionSentenceGeneratorFactory|null $descriptionSentenceGeneratorFactory
     * @param DescriptionParagraphGeneratorFactory|null $descriptionParagraphGeneratorFactory
     * @param DescriptionGeneratorFactory|null $descriptionGeneratorFactory
     * @param DictionaryFactory|null $dictionaryFactory
     * @param SearchTermManagerFactory|null $searchTermManagerFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FixtureConfig $fixtureConfig,
        ?DescriptionSentenceGeneratorFactory $descriptionSentenceGeneratorFactory = null,
        ?DescriptionParagraphGeneratorFactory $descriptionParagraphGeneratorFactory = null,
        ?DescriptionGeneratorFactory $descriptionGeneratorFactory = null,
        ?DictionaryFactory $dictionaryFactory = null,
        ?SearchTermManagerFactory $searchTermManagerFactory = null
    ) {
        $this->objectManager = $objectManager;
        $this->fixtureConfig = $fixtureConfig;
        $this->sentenceGeneratorFactory = $descriptionSentenceGeneratorFactory
            ?: $objectManager->get(DescriptionSentenceGeneratorFactory::class);
        $this->paragraphGeneratorFactory = $descriptionParagraphGeneratorFactory
            ?: $objectManager->get(DescriptionParagraphGeneratorFactory::class);
        $this->descriptionGeneratorFactory = $descriptionGeneratorFactory
            ?: $objectManager->get(DescriptionGeneratorFactory::class);
        $this->dictionaryFactory = $dictionaryFactory
            ?: $objectManager->get(DictionaryFactory::class);
        $this->searchTermManagerFactory = $searchTermManagerFactory
            ?: $objectManager->get(SearchTermManagerFactory::class);
    }

    /**
     * Search term description factory
     *
     * @param array|null $descriptionConfig
     * @param array|null $searchTermsConfig
     * @param int $totalProductsCount
     * @param string $defaultDescription
     * @return DescriptionGeneratorInterface
     */
    public function create(
        $descriptionConfig,
        $searchTermsConfig,
        $totalProductsCount,
        $defaultDescription = ''
    ) {
        $this->updateSearchTermConfig($searchTermsConfig);
        if (empty($descriptionConfig) || empty($searchTermsConfig)) {
            return $this->objectManager->create(
                DefaultDescriptionGenerator::class,
                ['defaultDescription' => $defaultDescription]
            );
        }
        return $this->objectManager->create(\Magento\Setup\Model\SearchTermDescriptionGenerator::class, [
            'descriptionGenerator' => $this->buildDescriptionGenerator($descriptionConfig),
            'searchTermManager' => $this->buildSearchTermManager($searchTermsConfig, $totalProductsCount)
        ]);
    }

    /**
     * Update search terms distribution to be almost the same per each website
     *
     * @param array|null $searchTermsConfig
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return void
     */
    private function updateSearchTermConfig(&$searchTermsConfig)
    {
        if (null !== $searchTermsConfig) {
            $websitesCount = (bool)$this->fixtureConfig->getValue('assign_entities_to_all_websites', false)
                ? 1
                : (int)$this->fixtureConfig->getValue('websites', 1);
            array_walk(
                $searchTermsConfig,
                function (&$searchTerm, $key, $websitesCount) {
                    $searchTerm['count'] *= $websitesCount;
                },
                $websitesCount
            );
        }
    }

    /**
     * Builder for DescriptionGenerator
     *
     * @param array $descriptionConfig
     * @return \Magento\Setup\Model\Description\DescriptionGenerator
     */
    private function buildDescriptionGenerator(array $descriptionConfig)
    {
        $sentenceGenerator = $this->sentenceGeneratorFactory->create([
            'dictionary' => $this->dictionaryFactory->create([
                'dictionaryFilePath' => realpath(__DIR__ . '/../Fixtures/_files/dictionary.csv')
            ]),
            'sentenceConfig' => $descriptionConfig['paragraphs']['sentences']
        ]);

        $paragraphGenerator = $this->paragraphGeneratorFactory->create([
            'sentenceGenerator' => $sentenceGenerator,
            'paragraphConfig' => $descriptionConfig['paragraphs']
        ]);

        $descriptionGenerator = $this->descriptionGeneratorFactory->create([
            'paragraphGenerator' => $paragraphGenerator,
            'mixinManager' => $this->objectManager->create(\Magento\Setup\Model\Description\MixinManager::class),
            'descriptionConfig' => $descriptionConfig
        ]);

        return $descriptionGenerator;
    }

    /**
     * Builder for SearchTermManager
     *
     * @param array $searchTermsConfig
     * @param int $totalProductsCount
     * @return \Magento\Setup\Model\SearchTermManager
     */
    private function buildSearchTermManager(array $searchTermsConfig, $totalProductsCount)
    {
        return $this->searchTermManagerFactory->create(
            [
                'searchTerms' => $searchTermsConfig,
                'totalProductsCount' => $totalProductsCount
            ]
        );
    }
}
