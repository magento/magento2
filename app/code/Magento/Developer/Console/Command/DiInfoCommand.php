<?php
/**
 * Created by PhpStorm.
 * @author Andra Lungu <andra.lungu@bitbull.it>
 * Date: 01/04/17
 * Time: 14.02
 */

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class DiInfoCommand extends Command
{
    /**
     * Command name
     */
    const COMMAND_NAME = 'dev:di:info';

    /**
     * input name
     */
    const CLASS_NAME = 'class';

    /**
     * @var Information
     */
    private $diInformation;

    /**
     * @param Information $diInformation
     */
    public function __construct(
        Information $diInformation
    ) {
        $this->diInformation = $diInformation;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
             ->setDescription('Generates the list of preferences for the class')
             ->setDefinition([
                new InputArgument(self::CLASS_NAME, InputArgument::REQUIRED, 'Class name')
            ]);

        parent::configure();
    }
}