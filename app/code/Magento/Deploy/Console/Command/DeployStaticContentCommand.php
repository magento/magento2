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

/**
 * Deploy static content command
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Key for dry-run option
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGE_OPTION = 'languages';

    /**
     * Key for exclude languages parameter
     */
    const EXCLUDE_LANGUAGE_OPTION = 'exclude-languages';

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
                    '-et',
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
                    '-el',
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
                    '-ea',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'List of areas you do not want the tool populate files for.',
                    ['none']
                ),
            ]);

        parent::configure();

    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $options = $input->getOptions();

        $excludeAreas = $input->getOption(self::EXCLUDE_AREA_OPTION);

        $areas = $input->getOption(self::AREA_OPTION);

        if ($excludeAreas[0] != 'none' && $areas[0] != 'all') {
            throw new \InvalidArgumentException(
                '--area (-a) and --exclude-area (-ea) cannot be used at the same time'
            );
        }

        $excludeLanguages = $input->getOption(self::EXCLUDE_LANGUAGE_OPTION);

        $languages = $input->getOption(self::LANGUAGE_OPTION);

        if ($excludeLanguages[0] != 'none' && $languages[0] != 'all') {
            throw new \InvalidArgumentException(
                '--language (-l) and --exclude-language (-el) cannot be used at the same time'
            );
        }

        $excludeThemes = $input->getOption(self::EXCLUDE_THEME_OPTION);

        $themes = $input->getOption(self::THEME_OPTION);

        if ($excludeThemes[0] != 'none' && $themes[0] != 'all') {
            throw new \InvalidArgumentException(
                '--theme (-t) and --exclude-theme (-et) cannot be used at the same time'
            );
        }

        $languages = $input->getArgument(self::LANGUAGE_OPTION);
        foreach ($languages as $lang) {

            if (!$this->validator->isValid($lang)) {
                throw new \InvalidArgumentException(
                    $lang . ' argument has invalid value, please run info:language:list for list of available locales'
                );
            }
        }

        // run the deployment logic
        $filesUtil = $this->objectManager->create(Files::class);

        $mageAreas = [];
        $mageThemes = [];
        $mageLanguages = ['en_US'];

        $files = $filesUtil->getStaticPreProcessingFiles();
        foreach ($files as $info) {
            list($area, $themePath, $locale) = $info;

            if ($themePath && !in_array($themePath, $mageThemes)) {
                $mageThemes[] = $themePath;
            }
            if ($locale && !in_array($locale, $mageLanguages)) {
                $mageLanguages[] = $locale;
            }
            if ($area && !in_array($area, $mageAreas)) {
                $mageAreas[] = $area;
            }
        }

        if ($languages[0] != 'all') {
            foreach ($languages as $locale) {
                if (!$this->validator->isValid($locale)) {
                    throw new \InvalidArgumentException(
                        $locale . ' argument has invalid value, please run info:language:list for list of available locales'
                    );
                }
            }
        }

        $deployLanguages = [];
        foreach ($mageLanguages as $locale) {
            if ($languages[0] != 'all' && in_array($locale, $languages)) {
                $deployLanguages[] = $locale;
            } elseif ($excludeLanguages[0] != 'none' && in_array($locale, $excludeLanguages)) {
                continue;
            } elseif ($languages[0] == 'all' && $excludeLanguages[0] == 'none') {
                $deployLanguages[] = $locale;
            }
        }

        if ($themes[0] != 'all') {
            foreach ($themes as $theme) {
                if (!in_array($theme, $mageThemes)) {
                    throw new \InvalidArgumentException(
                        $theme . ' argument has invalid value, avalilable themes are: ' . implode(', ', $mageThemes)
                    );
                }
            }
        }

        $deployThemes = [];
        foreach ($mageThemes as $theme) {
            if ($themes[0] != 'all' && in_array($theme, $themes)) {
                $deployThemes[] = $theme;
            } elseif ($excludeThemes[0] != 'none' && in_array($theme, $excludeThemes)) {
                continue;
            } elseif ($themes[0] == 'all' && $excludeThemes[0] == 'none') {
                $deployThemes[] = $theme;
            }
        }

        if ($areas[0] != 'all') {
            foreach ($areas as $area) {
                if (!in_array($area, $mageAreas)) {
                    throw new \InvalidArgumentException(
                        $area . ' argument has invalid value, avalilable areas are: ' . implode(', ', $mageAreas)
                    );
                }
            }
        }

        $deployAreas = [];
        foreach ($mageAreas as $area) {
            if ($areas[0] != 'all' && in_array($area, $areas)) {
                $deployAreas[] = $area;
            } elseif ($excludeAreas[0] != 'none' && in_array($area, $excludeAreas)) {
                continue;
            } elseif ($areas[0] == 'all' && $excludeAreas[0] == 'none') {
                $deployAreas[] = $area;
            }
        }

        $deployer = $this->objectManager->create(
            'Magento\Deploy\Model\Deployer',
            [
                'filesUtil' => $filesUtil,
                'output' => $output,
                'isDryRun' => $options[self::DRY_RUN_OPTION],
                'isJavaScript' => $options[self::JAVASCRIPT_OPTION],
                'isCss' => $options[self::CSS_OPTION],
                'isLess' => $options[self::LESS_OPTION],
                'isImages' => $options[self::IMAGES_OPTION],
                'isFonts' => $options[self::FONTS_OPTION],
                'isHtml' => $options[self::HTML_OPTION],
                'isMisc' => $options[self::MISC_OPTION],
                'isHtmlMinify' => $options[self::HTML_MINIFY_OPTION]
            ]
        );

        return $deployer->deploy($this->objectManagerFactory, $languages, $deployAreas, $deployLanguages, $deployThemes);
    }
}
