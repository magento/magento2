<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerStatusCommand;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Indexer Factory
     *
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * Collection Factory
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Command being tested
     *
     * @var IndexerStatusCommand
     */
    private $command;

    protected function setUp()
    {
        $objectManagerProvider = $this->getMock('Magento\Framework\Console\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\Console\ObjectManagerProvider', [], [], '', false);
        $customParameter = $this->getMock('Magento\Indexer\Console\CustomParameter', [], [], '', false);
        $collectionFactory = $this->getMock('Magento\Indexer\Model\Indexer\CollectionFactory', [], [], '', false);
        $indexerFactory = $this->getMock('Magento\Indexer\Model\IndexerFactory', [], [], '', false);

        $objectManagerProvider->expects($this->once())->method('get')->with($customParameter);
        $this->command = new IndexerStatusCommand($objectManagerProvider, $customParameter);
    }

    public function testExecute()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        echo $commandTester->getDisplay();
    }

//    public function testGetOptionsList()
//    {
//        /* @var $argsList \Symfony\Component\Console\Input\InputArgument[] */
//        $argsList = $this->command->getOptionsList();
//        $this->assertEquals(AdminAccount::KEY_EMAIL, $argsList[2]->getName());
//    }
//
//    /**
//     * @dataProvider validateDataProvider
//     * @param bool[] $options
//     * @param string[] $errors
//     */
//    public function testValidate(array $options, array $errors)
//    {
//        $inputMock = $this->getMockForAbstractClass('Symfony\Component\Console\Input\InputInterface', [], '', false);
//        $index = 0;
//        foreach ($options as $option) {
//            $inputMock->expects($this->at($index++))->method('getOption')->willReturn($option);
//        }
//        $this->assertEquals($errors, $this->command->validate($inputMock));
//    }
//
//    /**
//     * @return array
//     */
//    public function validateDataProvider()
//    {
//        return [
//            [[false, true, true, true, true], ['Missing option ' . AdminAccount::KEY_USER]],
//            [
//                [true, false, false, true, true],
//                ['Missing option ' . AdminAccount::KEY_PASSWORD, 'Missing option ' . AdminAccount::KEY_EMAIL],
//            ],
//            [[true, true, true, true, true], []],
//        ];
//    }
//    /**
//         * {@inheritdoc}
//         */
//    }
//    protected function configure()
//    {
//        $this->setName('indexer:status')
//            ->setDescription('Shows status of Indexer')
//            ->setDefinition($this->getOptionsList());
//        parent::configure();
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function execute(InputInterface $input, OutputInterface $output)
//    {
//        $indexers = $this->getIndexers($input);
//        foreach ($indexers as $indexer) {
//            $status = 'unknown';
//            switch ($indexer->getStatus()) {
//                case \Magento\Indexer\Model\Indexer\State::STATUS_VALID:
//                    $status = 'Ready';
//                    break;
//                case \Magento\Indexer\Model\Indexer\State::STATUS_INVALID:
//                    $status = 'Reindex required';
//                    break;
//                case \Magento\Indexer\Model\Indexer\State::STATUS_WORKING:
//                    $status = 'Processing';
//                    break;
//            }
//            $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $status);
//        }
//    }
}
