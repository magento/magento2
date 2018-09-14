<?php

namespace Magento\Sitemap\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapGenerateCommand extends Command
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
     * GenerateSitemapCommand constructor.
     * @param SitemapRepositoryInterface $sitemapRepository
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sitemap\Api\SitemapRepositoryInterface\Proxy $sitemapRepository
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription(
                'Generates sitemaps for the store.'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $sitemap = $this->sitemapRepository->getById(1);
        $sitemap->generateXml();
    }

}