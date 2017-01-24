<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Troubleshooting\Helper\UrlAnalyzer;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Analyze Magento Admin.
 */
class AdminAnalyzer extends \Symfony\Component\Console\Command\Command
{
    /**
     * Console output of formatted messages.
     *
     * @var \Magento\Mtf\Console\Output
     */
    private $output;

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Url analyzer helper.
     *
     * @var UrlAnalyzer
     */
    private $urlAnalyzer;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param UrlAnalyzer $urlAnalyzer
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        UrlAnalyzer $urlAnalyzer
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->urlAnalyzer = $urlAnalyzer;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:check-magento-admin')
            ->setDescription('Check that app_backend_url is correct and admin can log in to Admin.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \PHPUnit_Util_Configuration::getInstance(MTF_PHPUNIT_FILE)->handlePHPConfiguration();
        $this->output = $this->objectManager->create(
            \Magento\Mtf\Console\Output::class,
            ['output' => $output]
        );
        $this->output->writeln("Verifying Magento Admin...");
        $adminUrlAnalyzerMessages = $this->runAdminUrlAnalyzer();
        if (isset($adminUrlAnalyzerMessages['error']) === false) {
            $this->output->outputMessages($this->urlAnalyzer->checkDomain($_ENV['app_backend_url']));
        } else {
            $this->output->outputMessages($adminUrlAnalyzerMessages);
        }
        $this->output->writeln("Admin verification finished.");
    }

    /**
     * Execute Admin url analyzer check.
     *
     * @return null|array
     */
    public function runAdminUrlAnalyzer()
    {
        if (!isset($_ENV['app_backend_url'])) {
            $messages['error'][] = 'app_backend_url parameter is absent in the phpunit.xml file. '
                . 'Please, copy parameter from phpunit.xml.dist.';
            return $messages;
        }
        $this->output->outputMessages($this->urlAnalyzer->fixLastSlash('app_backend_url'));
        $url1 = $_ENV['app_backend_url'];
        if (strpos($url1, '/index.php') !== false) {
            $url2 = str_replace('/index.php', '', $url1);
        } else {
            $pattern = '/(\/\w+\/)$/';
            $replacement = '/index.php$1';
            $url2 = str_replace($url1, preg_replace($pattern, $replacement, $url1), $url1);
        }
        $urls = [$url1, $url2];
        $isUrlValid = false;
        foreach ($urls as $url) {
            $_ENV['app_backend_url'] = $url;
            try {
                $config = \Magento\Mtf\ObjectManagerFactory::getObjectManager()->create(
                    \Magento\Mtf\Config\DataInterface::class
                );
                $curl = new BackendDecorator(new CurlTransport(), $config);
                $response = $curl->read();
                if (strpos($response, '404') !== false) {
                    break;
                }
                $curl->close();
                $isUrlValid = true;
                break;
            } catch (\Exception $e) {
                continue;
            }
        }
        if ($isUrlValid == false) {
            $messages['error'][] = 'Check correctness of app_backend_url in phpunit.xml.';
            return $messages;
        } elseif ($url1 != $_ENV['app_backend_url']) {
            return $this->urlAnalyzer->resolveIndexPhpProblem($_ENV['app_backend_url']);
        }
    }
}
