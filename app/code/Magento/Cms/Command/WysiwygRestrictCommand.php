<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Command;

use Magento\Cms\Model\Wysiwyg\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigWriter;
use Magento\Framework\App\Cache\TypeListInterface as Cache;

/**
 * Command to toggle WYSIWYG content validation on/off.
 */
class WysiwygRestrictCommand extends Command
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param ConfigWriter $configWriter
     * @param Cache $cache
     */
    public function __construct(ConfigWriter $configWriter, Cache $cache)
    {
        parent::__construct();

        $this->configWriter = $configWriter;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('cms:wysiwyg:restrict');
        $this->setDescription('Set whether to enforce user HTML content validation or show a warning instead');
        $this->setDefinition([new InputArgument('restrict', InputArgument::REQUIRED, 'y\n')]);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $restrictArg = mb_strtolower((string)$input->getArgument('restrict'));
        $restrict = $restrictArg === 'y' ? '1' : '0';
        $this->configWriter->saveConfig(Validator::CONFIG_PATH_THROW_EXCEPTION, $restrict);
        $this->cache->cleanType('config');

        $output->writeln('HTML user content validation is now ' .($restrictArg === 'y' ? 'enforced' : 'suggested'));

        return 0;
    }
}
