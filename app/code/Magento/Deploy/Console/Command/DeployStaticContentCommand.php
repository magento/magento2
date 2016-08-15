<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
use Magento\Framework\Console\Cli;
use Magento\Deploy\Model\ProcessManager;
use Magento\Deploy\Model\Process;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;

/**
 * Deploy static content command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Default language value
     */
    const DEFAULT_LANGUAGE_VALUE = 'en_US';

    /**
     * Key for dry-run option
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGES_ARGUMENT = 'languages';

    /**
     * Key for languages parameter
     */
    const LANGUAGE_OPTION = 'language';

    /**
     * Key for exclude languages parameter
     */
    const EXCLUDE_LANGUAGE_OPTION = 'exclude-language';

    /**
     * Key for javascript option
     */
    const JAVASCRIPT_OPTION = 'no-javascript';

    /**
     * Key for css option
     */
    const CSS_OPTION = 'no-css';

    /**
     * Key for less option
     */
    const LESS_OPTION = 'no-less';

    /**
     * Key for images option
     */
    const IMAGES_OPTION = 'no-images';

    /**
     * Key for fonts option
     */
    const FONTS_OPTION = 'no-fonts';

    /**
     * Key for misc option
     */
    const MISC_OPTION = 'no-misc';

    /**
     * Key for html option
     */
    const HTML_OPTION = 'no-html';

    /**
     * Key for html option
     */
    const HTML_MINIFY_OPTION = 'no-html-minify';

    /**
     * Key for theme option
     */
    const THEME_OPTION = 'theme';

    /**
     * Key for exclude theme option
     */
    const EXCLUDE_THEME_OPTION = 'exclude-theme';

    /**
     * Key for area option
     */
    const AREA_OPTION = 'area';

    /**
     * Key for exclude area option
     */
    const EXCLUDE_AREA_OPTION = 'exclude-area';

    /**
     * Jey for jobs option
     */
    const JOBS_AMOUNT = 'jobs';

    /**
     * Default jobs amount
     */
    const DEFAULT_JOBS_AMOUNT = 3;

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

    /** @var \Magento\Framework\App\State */
    private $appState;

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
                    self::DRY_RUN_OPTION,
                    '-d',
                    InputOption::VALUE_NONE,
                    'If specified, then no files will be actually deployed.'
                ),
                new InputOption(
                    self::JAVASCRIPT_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no JavaScript will be deployed.'
                ),
                new InputOption(
                    self::CSS_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no CSS will be deployed.'
                ),
                new InputOption(
                    self::LESS_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no LESS will be deployed.'
                ),
                new InputOption(
                    self::IMAGES_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no images will be deployed.'
                ),
                new InputOption(
                    self::FONTS_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no font files will be deployed.'
                ),
                new InputOption(
                    self::HTML_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no html files will be deployed.'
                ),
                new InputOption(
                    self::MISC_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no miscellaneous files will be deployed.'
                ),
                new InputOption(
                    self::HTML_MINIFY_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, just html will not be minified and actually deployed.'
                ),
                new InputOption(
                    self::THEME_OPTION,
                    '-t',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'If specified, just specific theme(s) will be actually deployed.',
                    ['all']
                ),
                new InputOption(
                    self::EXCLUDE_THEME_OPTION,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'If specified, exclude specific theme(s) from deployment.',
                    ['none']
                ),
                new InputOption(
                    self::LANGUAGE_OPTION,
                    '-l',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'List of languages you want the tool populate files for.',
                    ['all']
                ),
                new InputOption(
                    self::EXCLUDE_LANGUAGE_OPTION,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'List of langiages you do not want the tool populate files for.',
                    ['none']
                ),
                new InputOption(
                    self::AREA_OPTION,
                    '-a',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'List of areas you want the tool populate files for.',
                    ['all']
                ),
                new InputOption(
                    self::EXCLUDE_AREA_OPTION,
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'List of areas you do not want the tool populate files for.',
                    ['none']
                ),
                new InputOption(
                    self::JOBS_AMOUNT,
                    '-j',
                    InputOption::VALUE_OPTIONAL,
                    'Amount of jobs to which script can be paralleled.',
                    self::DEFAULT_JOBS_AMOUNT
                ),
                new InputArgument(
                    self::LANGUAGES_ARGUMENT,
                    InputArgument::IS_ARRAY,
                    'List of languages you want the tool populate files for.'
                ),
            ]);

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
        if ($this->getAppState()->getMode() !== State::MODE_PRODUCTION) {
            throw new LocalizedException(
                __(
                    "Deploy static content is applicable only for production mode.\n"
                    . "Please use command 'bin/magento deploy:mode:set production' for set up production mode."
                )
            );
        }

        $this->input = $input;
        $filesUtil = $this->objectManager->create(Files::class);

        list ($deployableLanguages, $deployableAreaThemeMap, $requestedThemes)
            = $this->prepareDeployableEntities($filesUtil);

        $output->writeln("Requested languages: " . implode(', ', $deployableLanguages));
        $output->writeln("Requested areas: " . implode(', ', array_keys($deployableAreaThemeMap)));
        $output->writeln("Requested themes: " . implode(', ', $requestedThemes));

        $options = $this->input->getOptions();
        $deployer = $this->objectManager->create(
            \Magento\Deploy\Model\Deployer::class,
            [
                'filesUtil' => $filesUtil,
                'output' => $output,
                'isDryRun' => $options[self::DRY_RUN_OPTION],
                'skipJavaScript' => $options[self::JAVASCRIPT_OPTION],
                'skipCss' => $options[self::CSS_OPTION],
                'skipLess' => $options[self::LESS_OPTION],
                'skipImages' => $options[self::IMAGES_OPTION],
                'skipFonts' => $options[self::FONTS_OPTION],
                'skipHtml' => $options[self::HTML_OPTION],
                'skipMisc' => $options[self::MISC_OPTION],
                'skipHtmlMinify' => $options[self::HTML_MINIFY_OPTION]
            ]
        );

        if ($this->isCanBeParalleled()) {
            return $this->runProcessesInParallel($deployer, $deployableAreaThemeMap, $deployableLanguages);
        } else {
            return $this->deploy($deployer, $deployableLanguages, $deployableAreaThemeMap);
        }
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

        $areasInclude = $this->input->getOption(self::AREA_OPTION);
        $areasExclude = $this->input->getOption(self::EXCLUDE_AREA_OPTION);
        $this->checkAreasInput($magentoAreas, $areasInclude, $areasExclude);
        $deployableAreas = $this->getDeployableEntities($magentoAreas, $areasInclude, $areasExclude);

        $languagesInclude = $this->input->getArgument(self::LANGUAGES_ARGUMENT)
            ?: $this->input->getOption(self::LANGUAGE_OPTION);
        $languagesExclude = $this->input->getOption(self::EXCLUDE_LANGUAGE_OPTION);
        $this->checkLanguagesInput($languagesInclude, $languagesExclude);
        $deployableLanguages = $languagesInclude[0] == 'all'
            ? $this->getDeployableEntities($magentoLanguages, $languagesInclude, $languagesExclude)
            : $languagesInclude;

        $themesInclude = $this->input->getOption(self::THEME_OPTION);
        $themesExclude = $this->input->getOption(self::EXCLUDE_THEME_OPTION);
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
     * @param \Magento\Deploy\Model\Deployer $deployer
     * @param array $deployableLanguages
     * @param array $deployableAreaThemeMap
     * @return int
     */
    private function deploy($deployer, $deployableLanguages, $deployableAreaThemeMap)
    {
        return $deployer->deploy(
            $this->objectManagerFactory,
            $deployableLanguages,
            $deployableAreaThemeMap
        );
    }

    /**
     * @param \Magento\Deploy\Model\Deployer $deployer
     * @param array $deployableAreaThemeMap
     * @param array $deployableLanguages
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function runProcessesInParallel($deployer, $deployableAreaThemeMap, $deployableLanguages)
    {
        /** @var ProcessManager $processManager */
        $processManager = $this->objectManager->create(ProcessManager::class);
        $processNumber = 0;
        $processQueue = [];
        foreach ($deployableAreaThemeMap as $area => &$themes) {
            foreach ($themes as $theme) {
                foreach ($deployableLanguages as $lang) {
                    $deployerFunc = function (Process $process) use ($area, $theme, $lang, $deployer) {
                        return $this->deploy($deployer, [$lang], [$area => [$theme]]);
                    };
                    if ($processNumber >= $this->getProcessesAmount()) {
                        $processQueue[] = $deployerFunc;
                    } else {
                        $processManager->fork($deployerFunc);
                    }
                    $processNumber++;
                }
            }
        }
        $returnStatus = null;
        while (count($processManager->getProcesses()) > 0) {
            foreach ($processManager->getProcesses() as $process) {
                if ($process->isCompleted()) {
                    $processManager->delete($process);
                    $returnStatus |= $process->getStatus();
                    if ($queuedProcess = array_shift($processQueue)) {
                        $processManager->fork($queuedProcess);
                    }
                    if (count($processManager->getProcesses()) >= $this->getProcessesAmount()) {
                        break 1;
                    }
                }
            }
            usleep(5000);
        }

        return $returnStatus === Cli::RETURN_SUCCESS ?: Cli::RETURN_FAILURE;
    }

    /**
     * @return bool
     */
    private function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->getProcessesAmount() > 1;
    }

    /**
     * @return int
     */
    private function getProcessesAmount()
    {
        $jobs = (int)$this->input->getOption(self::JOBS_AMOUNT);
        if ($jobs < 1) {
            throw new \InvalidArgumentException(
                self::JOBS_AMOUNT . ' argument has invalid value. It must be greater than 0'
            );
        }
        return $jobs;
    }
}
