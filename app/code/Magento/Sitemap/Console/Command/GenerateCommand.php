<?php

namespace Magento\Sitemap\Console\Command;

use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Sitemap\Api\SitemapRepositoryInterface;
use Magento\Sitemap\Model\XmlGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{

    /** Command name */
    const NAME = 'sitemap:generate';
    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var SearchCriteriaFactory
     */
    private $criteriaFactory;

    /**
     * GenerateSitemapCommand constructor.
     * @param SitemapRepositoryInterface $sitemapRepository
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sitemap\Api\SitemapRepositoryInterface\Proxy $sitemapRepository,
        SearchCriteriaFactory $criteriaFactory
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->state = $state;
        $this->criteriaFactory = $criteriaFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription(
                'Generates sitemap for the store.'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $sitemaps = $this->sitemapRepository->getList($this->criteriaFactory->create());
        $progressbar = new ProgressBar($output, $sitemaps->getTotalCount());
        $progressbar->setFormat("<info>%message%</info> %current%/%max% [%bar%] %percent:3s%% %elapsed%");

        $output->writeln('<info>Generation was started.</info>');
        $progressbar->start();

        foreach ($sitemaps->getItems() as $sitemap) {
            $progressbar->setMessage('Generating sitemap ' . $sitemap->getId() . ' ...');
            $progressbar->display();
            $sitemap->generateXml();
            $progressbar->advance();
        }
        $progressbar->finish();
        $output->writeln('');
        $output->writeln('<info>Generated store sitemaps successfully.</info>');

    }

}