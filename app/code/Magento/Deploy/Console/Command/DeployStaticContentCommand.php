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
     * Key for themes option
     */
    const THEMES_OPTION = 'themes';

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
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no JavaScript will be deployed.'
                ),
                new InputOption(
                    self::CSS_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no CSS will be deployed.'
                ),
                new InputOption(
                    self::LESS_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no LESS will be deployed.'
                ),
                new InputOption(
                    self::IMAGES_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no images will be deployed.'
                ),
                new InputOption(
                    self::FONTS_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no font files will be deployed.'
                ),
                new InputOption(
                    self::HTML_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no html files will be deployed.'
                ),
                new InputOption(
                    self::MISC_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, no miscellaneous files will be deployed.'
                ),
                new InputOption(
                    self::HTML_MINIFY_OPTION,
                    '',
                    InputOption::VALUE_NONE,
                    'If specified, just html will not be minified and actually deployed.'
                ),
                new InputOption(
                    self::THEMES_OPTION,
                    '-t',
                    InputOption::VALUE_IS_ARRAY + InputOption::VALUE_OPTIONAL,
                    'If specified, just specific themes will be actually deployed.'
                ),
                new InputArgument(
                    self::LANGUAGE_OPTION,
                    InputArgument::IS_ARRAY,
                    'List of languages you want the tool populate files for.',
                    ['en_US']
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

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

        $mageThemes = [];
        $files = $filesUtil->getStaticPreProcessingFiles();
        foreach ($files as $info) {
            list(, $themePath) = $info;
            if ($themePath && !in_array($themePath, $mageThemes)) {
                $mageThemes[] = $themePath;
            }
        }

        $themes = $input->getOption(self::THEMES_OPTION);

        foreach ($themes as $theme) {

            if ($theme != 'all' && !in_array($theme, $mageThemes)) {
                throw new \InvalidArgumentException(
                    $theme . ' argument has invalid value, avalilable areas are: ' . implode(', ', $mageThemes)
                );
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

        $deployer->deploy($this->objectManagerFactory, $languages, $themes);
    }
}
