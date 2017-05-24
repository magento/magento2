<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Search Term Description Generator Factory
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Setup\Fixtures\FixtureConfig $fixtureConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Setup\Fixtures\FixtureConfig $fixtureConfig
    ) {
        $this->objectManager = $objectManager;
        $this->fixtureConfig = $fixtureConfig;
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
        $sentenceGeneratorFactory = $this->objectManager->create(
            \Magento\Setup\Model\Description\DescriptionSentenceGeneratorFactory::class
        );
        $paragraphGeneratorFactory = $this->objectManager->create(
            \Magento\Setup\Model\Description\DescriptionParagraphGeneratorFactory::class
        );
        $descriptionGeneratorFactory = $this->objectManager->create(
            \Magento\Setup\Model\Description\DescriptionGeneratorFactory::class
        );
        $dictionaryFactory = $this->objectManager->create(
            \Magento\Setup\Model\DictionaryFactory::class
        );

        $sentenceGenerator = $sentenceGeneratorFactory->create([
            'dictionary' => $dictionaryFactory->create([
                'dictionaryFilePath' => realpath(__DIR__ . '/../Fixtures/_files/dictionary.csv')
            ]),
            'sentenceConfig' => $descriptionConfig['paragraphs']['sentences']
        ]);

        $paragraphGenerator = $paragraphGeneratorFactory->create([
            'sentenceGenerator' => $sentenceGenerator,
            'paragraphConfig' => $descriptionConfig['paragraphs']
        ]);

        $descriptionGenerator = $descriptionGeneratorFactory->create([
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
        $searchTermManagerFactory = $this->objectManager->get(
            \Magento\Setup\Model\SearchTermManagerFactory::class
        );

        return $searchTermManagerFactory->create([
            'searchTerms' => $searchTermsConfig,
            'totalProductsCount' => $totalProductsCount
        ]);
    }
}
