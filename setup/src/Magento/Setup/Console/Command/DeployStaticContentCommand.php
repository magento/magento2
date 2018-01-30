<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\DeployStaticOptions as Options;
use Magento\Deploy\Model\DeployManager;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Locale;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\Type\Dummy as DummyCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy static content command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated since 2.2.0 @see \Magento\Setup\Console\Command\Deploy\StaticContentCommand
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Key for dry-run option
     *
     * @deprecated
     * @see \Magento\Deploy\Console\Command\DeployStaticOptions::DRY_RUN
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Default language value
     */
    const DEFAULT_LANGUAGE_VALUE = 'en_US';

    /**
     * Key for languages parameter
     */
    const LANGUAGES_ARGUMENT = 'languages';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var Locale
     */
    private $validator;

    /**
     * @var Options
     */
    private $options;

    /**
     * Object manager to create various objects
     *
     * @var ObjectManagerInterface
     *
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * Inject dependencies
     *
     * @param Locale                $validator
     * @param Options               $options
     * @param ObjectManagerProvider $objectManagerProvider
     * @throws \LogicException When the command name is empty
     */
    public function __construct(
        Locale $validator,
        Options $options,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->validator = $validator;
        $this->options = $options;
        $this->objectManager = $objectManagerProvider->get();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('setup:static-content:deploy:old')
            ->setDescription('Deploys static view files')
            ->setDefinition($this->options->getOptionsList());

        parent::configure();
    }

    /**
     * @return \Magento\Framework\App\State
     * @deprecated
     */
    private function getAppState()
    {
        if (null === $this->appState) {
            $this->appState = $this->objectManager->get(State::class);
        }
        return $this->appState;
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
     * @param $entities         array
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
            $deployableEntities = array_diff($entities, $excludedEntities);
        } elseif ($includedEntities[0] !== 'all') {
            $deployableEntities = array_intersect($entities, $includedEntities);
        }

        return $deployableEntities;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(Options::FORCE_RUN) && $this->getAppState()->getMode() !== State::MODE_PRODUCTION) {
            throw new LocalizedException(
                __(
                    'NOTE: Manual static content deployment is not required in "default" and "developer" modes.'
                    . PHP_EOL . 'In "default" and "developer" modes static contents are being deployed '
                    . 'automatically on demand.'
                    . PHP_EOL . 'If you still want to deploy in these modes, use -f option: '
                    . "'bin/magento setup:static-content:deploy -f'"
                )
            );
        }

        $this->input = $input;
        $filesUtil = $this->objectManager->create(Files::class);

        list ($deployableLanguages, $deployableAreaThemeMap, $requestedThemes)
            = $this->prepareDeployableEntities($filesUtil);

        $output->writeln('Requested languages: ' . implode(', ', $deployableLanguages));
        $output->writeln('Requested areas: ' . implode(', ', array_keys($deployableAreaThemeMap)));
        $output->writeln('Requested themes: ' . implode(', ', $requestedThemes));

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
     * @throws \InvalidArgumentException
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
