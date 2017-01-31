<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

use Magento\Framework\App\Utility\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Locale;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Deploy\Model\DeployManager;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\Type\Dummy as DummyCache;

/**
 * Deploy static content command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Key for dry-run option
     * @deprecated
     * @see Magento\Deploy\Console\Command\DeployStaticOptionsInterface::DRY_RUN
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     * @deprecated
     * @see DeployStaticContentCommand::LANGUAGES_ARGUMENT
     */
    const LANGUAGE_OPTION = 'languages';

    /**
     * Default language value
     */
    const DEFAULT_LANGUAGE_VALUE = 'en_US';

    /**
     * Key for languages parameter
     */
    const LANGUAGES_ARGUMENT = 'languages';

    /**
     * Default jobs amount
     */
    const DEFAULT_JOBS_AMOUNT = 4;

    /** @var InputInterface */
    private $input;

    /**
     * @var Locale
     */
    private $validator;

    /**
     * Factory to get object manager
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * object manager to create various objects
     *
     * @var ObjectManagerInterface
     *
     */
    private $objectManager;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param Locale $validator
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        Locale $validator,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->validator = $validator;
        $this->objectManager = $objectManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configure()
    {
        $this->setName('setup:static-content:deploy')
            ->setDescription('Deploys static view files')
            ->setDefinition([
                new InputOption(
                    Options::DRY_RUN,
                    '-d',
                    InputOption::VALUE_NONE,
                    'If specified, then no files will be actually deployed.'
                ),
                new InputOption(
                    Options::NO_JAVASCRIPT,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy JavaScript files'
                ),
                new InputOption(
                    Options::NO_CSS,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy CSS files.'
                ),
                new InputOption(
                    Options::NO_LESS,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy LESS files.'
                ),
                new InputOption(
                    Options::NO_IMAGES,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy images.'
                ),
                new InputOption(
                    Options::NO_FONTS,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy font files.'
                ),
                new InputOption(
                    Options::NO_HTML,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy HTML files.'
                ),
                new InputOption(
                    Options::NO_MISC,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not deploy other types of files (.md, .jbf, .csv, etc...).'
                ),
                new InputOption(
                    Options::NO_HTML_MINIFY,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not minify HTML files.'
                ),
                new InputOption(
                    Options::THEME,
                    '-t',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Generate static view files for only the specified themes.',
                    ['all']
                ),
                new InputOption(
                    Options::EXCLUDE_THEME,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Do not generate files for the specified themes.',
                    ['none']
                ),
                new InputOption(
                    Options::LANGUAGE,
                    '-l',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Generate files only for the specified languages.',
                    ['all']
                ),
                new InputOption(
                    Options::EXCLUDE_LANGUAGE,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Do not generate files for the specified languages.',
                    ['none']
                ),
                new InputOption(
                    Options::AREA,
                    '-a',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Generate files only for the specified areas.',
                    ['all']
                ),
                new InputOption(
                    Options::EXCLUDE_AREA,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Do not generate files for the specified areas.',
                    ['none']
                ),
                new InputOption(
                    Options::JOBS_AMOUNT,
                    '-j',
                    InputOption::VALUE_OPTIONAL,
                    'Enable parallel processing using the specified number of jobs.',
                    self::DEFAULT_JOBS_AMOUNT
                ),
                new InputOption(
                    Options::SYMLINK_LOCALE,
                    null,
                    InputOption::VALUE_NONE,
                    'Create symlinks for the files of those locales, which are passed for deployment, '
                    . 'but have no customizations'
                ),
                new InputArgument(
                    self::LANGUAGES_ARGUMENT,
                    InputArgument::IS_ARRAY,
                    'Space-separated list of ISO-636 language codes for which to output static view files.'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @param $magentoAreas array
     * @param $areasInclude array
     * @param $areasExclude array
     * @throws \InvalidArgumentException
     */
    private function checkAreasInput($magentoAreas, $areasInclude, $areasExclude)
    {
        if ($areasInclude[0] != 'all' && $areasExclude[0] != 'none') {
            throw new \InvalidArgumentException(
                '--area (-a) and --exclude-area cannot be used at the same time'
            );
        }

        if ($areasInclude[0] != 'all') {
            foreach ($areasInclude as $area) {
                if (!in_array($area, $magentoAreas)) {
                    throw new \InvalidArgumentException(
                        $area .
                        ' argument has invalid value, available areas are: ' . implode(', ', $magentoAreas)
                    );
                }
            }
        }

        if ($areasExclude[0] != 'none') {
            foreach ($areasExclude as $area) {
                if (!in_array($area, $magentoAreas)) {
                    throw new \InvalidArgumentException(
                        $area .
                        ' argument has invalid value, available areas are: ' . implode(', ', $magentoAreas)
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @param $languagesInclude array
     * @param $languagesExclude array
     * @throws \InvalidArgumentException
     */
    private function checkLanguagesInput($languagesInclude, $languagesExclude)
    {
        if ($languagesInclude[0] != 'all') {
            foreach ($languagesInclude as $lang) {
                if (!$this->validator->isValid($lang)) {
                    throw new \InvalidArgumentException(
                        $lang .
                        ' argument has invalid value, please run info:language:list for list of available locales'
                    );
                }
            }
        }

        if ($languagesInclude[0] != 'all' && $languagesExclude[0] != 'none') {
            throw new \InvalidArgumentException(
                '--language (-l) and --exclude-language cannot be used at the same time'
            );
        }
    }

    /**
     * {@inheritdoc}
     * @param $magentoThemes array
     * @param $themesInclude array
     * @param $themesExclude array
     * @throws \InvalidArgumentException
     */
    private function checkThemesInput($magentoThemes, $themesInclude, $themesExclude)
    {
        if ($themesInclude[0] != 'all' && $themesExclude[0] != 'none') {
            throw new \InvalidArgumentException(
                '--theme (-t) and --exclude-theme cannot be used at the same time'
            );
        }

        if ($themesInclude[0] != 'all') {
            foreach ($themesInclude as $theme) {
                if (!in_array($theme, $magentoThemes)) {
                    throw new \InvalidArgumentException(
                        $theme .
                        ' argument has invalid value, available themes are: ' . implode(', ', $magentoThemes)
                    );
                }
            }
        }

        if ($themesExclude[0] != 'none') {
            foreach ($themesExclude as $theme) {
                if (!in_array($theme, $magentoThemes)) {
                    throw new \InvalidArgumentException(
                        $theme .
                        ' argument has invalid value, available themes are: ' . implode(', ', $magentoThemes)
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @param $entities array
     * @param $includedEntities array
     * @param $excludedEntities array
     * @return array
     */
    private function getDeployableEntities($entities, $includedEntities, $excludedEntities)
    {
        $deployableEntities = [];
        if ($includedEntities[0] === 'all' && $excludedEntities[0] === 'none') {
            $deployableEntities = $entities;
        } elseif ($excludedEntities[0] !== 'none') {
            $deployableEntities =  array_diff($entities, $excludedEntities);
        } elseif ($includedEntities[0] !== 'all') {
            $deployableEntities =  array_intersect($entities, $includedEntities);
        }

        return $deployableEntities;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $filesUtil = $this->objectManager->create(Files::class);

        list ($deployableLanguages, $deployableAreaThemeMap, $requestedThemes)
            = $this->prepareDeployableEntities($filesUtil);

        $output->writeln("Requested languages: " . implode(', ', $deployableLanguages));
        $output->writeln("Requested areas: " . implode(', ', array_keys($deployableAreaThemeMap)));
        $output->writeln("Requested themes: " . implode(', ', $requestedThemes));

        /** @var $deployManager DeployManager */
        $deployManager = $this->objectManager->create(
            DeployManager::class,
            [
                'output' => $output,
                'options' => $this->input->getOptions(),
            ]
        );

        foreach ($deployableAreaThemeMap as $area => $themes) {
            foreach ($deployableLanguages as $locale) {
                foreach ($themes as $themePath) {
                    $deployManager->addPack($area, $themePath, $locale);
                }
            }
        }

        $this->mockCache();
        return $deployManager->deploy();
    }

    /**
     * @param Files $filesUtil
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareDeployableEntities($filesUtil)
    {
        $magentoAreas = [];
        $magentoThemes = [];
        $magentoLanguages = [self::DEFAULT_LANGUAGE_VALUE];
        $areaThemeMap = [];
        $files = $filesUtil->getStaticPreProcessingFiles();
        foreach ($files as $info) {
            list($area, $themePath, $locale) = $info;
            if ($themePath) {
                $areaThemeMap[$area][$themePath] = $themePath;
            }
            if ($themePath && $area && !in_array($area, $magentoAreas)) {
                $magentoAreas[] = $area;
            }
            if ($locale && !in_array($locale, $magentoLanguages)) {
                $magentoLanguages[] = $locale;
            }
            if ($themePath && !in_array($themePath, $magentoThemes)) {
                $magentoThemes[] = $themePath;
            }
        }

        $areasInclude = $this->input->getOption(Options::AREA);
        $areasExclude = $this->input->getOption(Options::EXCLUDE_AREA);
        $this->checkAreasInput($magentoAreas, $areasInclude, $areasExclude);
        $deployableAreas = $this->getDeployableEntities($magentoAreas, $areasInclude, $areasExclude);

        $languagesInclude = $this->input->getArgument(self::LANGUAGES_ARGUMENT)
            ?: $this->input->getOption(Options::LANGUAGE);
        $languagesExclude = $this->input->getOption(Options::EXCLUDE_LANGUAGE);
        $this->checkLanguagesInput($languagesInclude, $languagesExclude);
        $deployableLanguages = $languagesInclude[0] == 'all'
            ? $this->getDeployableEntities($magentoLanguages, $languagesInclude, $languagesExclude)
            : $languagesInclude;

        $themesInclude = $this->input->getOption(Options::THEME);
        $themesExclude = $this->input->getOption(Options::EXCLUDE_THEME);
        $this->checkThemesInput($magentoThemes, $themesInclude, $themesExclude);
        $deployableThemes = $this->getDeployableEntities($magentoThemes, $themesInclude, $themesExclude);

        $deployableAreaThemeMap = [];
        $requestedThemes = [];
        foreach ($areaThemeMap as $area => $themes) {
            if (in_array($area, $deployableAreas) && $themes = array_intersect($themes, $deployableThemes)) {
                $deployableAreaThemeMap[$area] = $themes;
                $requestedThemes += $themes;
            }
        }

        return [$deployableLanguages, $deployableAreaThemeMap, $requestedThemes];
    }

    /**
     * Mock Cache class with dummy implementation
     *
     * @return void
     */
    private function mockCache()
    {
        $this->objectManager->configure([
            'preferences' => [
                Cache::class => DummyCache::class
            ]
        ]);
    }
}
