<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Core layout utility
 */
namespace Magento\Framework\View\Utility;

/**
 * The integration testsuite for Layout view utility
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Layout
{
    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $_testCase;

    /**
     * @param \PHPUnit\Framework\TestCase $testCase
     */
    public function __construct(\PHPUnit\Framework\TestCase $testCase)
    {
        $this->_testCase = $testCase;
    }

    /**
     * Retrieve new layout update model instance with XML data from a fixture file
     *
     * @param string|array $layoutUpdatesFile
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function getLayoutUpdateFromFixture($layoutUpdatesFile)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\View\File\Factory $fileFactory */
        $fileFactory = $objectManager->get(\Magento\Framework\View\File\Factory::class);
        $files = [];
        foreach ((array)$layoutUpdatesFile as $filename) {
            $files[] = $fileFactory->create($filename, 'Magento_View');
        }
        // Use reflection to call protected getMockBuilder from TestCase (PHPUnit 12 compatibility)
        $reflection = new \ReflectionClass($this->_testCase);
        $getMockBuilderMethod = $reflection->getMethod('getMockBuilder');
        
        $fileSourceBuilder = $getMockBuilderMethod->invoke($this->_testCase, \Magento\Framework\View\File\CollectorInterface::class);
        $fileSourceBuilder->disableOriginalConstructor();
        $fileSource = $fileSourceBuilder->getMock();
        $fileSource->method('getFiles')->willReturn($files);
        
        $pageLayoutFileSourceBuilder = $getMockBuilderMethod->invoke($this->_testCase, \Magento\Framework\View\File\CollectorInterface::class);
        $pageLayoutFileSourceBuilder->disableOriginalConstructor();
        $pageLayoutFileSource = $pageLayoutFileSourceBuilder->getMock();
        $pageLayoutFileSource->method('getFiles')->willReturn([]);
        
        $cacheBuilder = $getMockBuilderMethod->invoke($this->_testCase, \Magento\Framework\Cache\FrontendInterface::class);
        $cacheBuilder->disableOriginalConstructor();
        $cache = $cacheBuilder->getMock();
        return $objectManager->create(
            \Magento\Framework\View\Layout\ProcessorInterface::class,
            ['fileSource' => $fileSource, 'pageLayoutFileSource' => $pageLayoutFileSource, 'cache' => $cache]
        );
    }

    /**
     * Retrieve new layout model instance with layout updates from a fixture file
     *
     * @param string|array $layoutUpdatesFile
     * @param array $args
     * @return \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLayoutFromFixture($layoutUpdatesFile, array $args = [])
    {
        $reflection = new \ReflectionClass($this->_testCase);
        $getMockBuilderMethod = $reflection->getMethod('getMockBuilder');
        $mockBuilder = $getMockBuilderMethod->invoke($this->_testCase, \Magento\Framework\View\Layout::class);
        
        $mockBuilder->onlyMethods(['getUpdate'])
            ->setConstructorArgs($args);
        $layout = $mockBuilder->getMock();
        $layoutUpdate = $this->getLayoutUpdateFromFixture($layoutUpdatesFile);
        $layoutUpdate->asSimplexml();
        $layout->method('getUpdate')->willReturn($layoutUpdate);
        return $layout;
    }

    /**
     * Retrieve object that will be used for layout instantiation
     *
     * @return array
     */
    public function getLayoutDependencies()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        return [
            'processorFactory' => $objectManager->get(\Magento\Framework\View\Layout\ProcessorFactory::class),
            'eventManager' => $objectManager->get(\Magento\Framework\Event\ManagerInterface::class),
            'structure' => $objectManager->create(\Magento\Framework\View\Layout\Data\Structure::class, []),
            'messageManager' => $objectManager->get(\Magento\Framework\Message\ManagerInterface::class),
            'themeResolver' => $objectManager->get(\Magento\Framework\View\Design\Theme\ResolverInterface::class),
            'readerPool' => $objectManager->get('commonRenderPool'),
            'generatorPool' => $objectManager->get(\Magento\Framework\View\Layout\GeneratorPool::class),
            'cache' => $objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class),
            'readerContextFactory' => $objectManager->get(\Magento\Framework\View\Layout\Reader\ContextFactory::class),
            'generatorContextFactory' => $objectManager->get(
                \Magento\Framework\View\Layout\Generator\ContextFactory::class
            ),
            'appState' => $objectManager->get(\Magento\Framework\App\State::class),
            'logger' => $objectManager->get(\Psr\Log\LoggerInterface::class),
        ];
    }
}
