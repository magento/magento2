<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency;

use Magento\Framework\File\Csv;
use Magento\Tools\Dependency\Circular as CircularTool;
use Magento\Tools\Dependency\Report\Circular as CircularReport;
use Magento\Tools\Dependency\Report\Dependency;
use Magento\Tools\Dependency\Report\Framework;

/**
 * Service Locator (instead DI container)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceLocator
{
    /**
     * Xml config dependencies parser
     *
     * @var \Magento\Tools\Dependency\ParserInterface
     */
    private static $xmlConfigParser;

    /**
     * Composer Json parser
     *
     * @var \Magento\Tools\Dependency\ParserInterface
     */
    private static $composerJsonParser;

    /**
     * Framework dependencies parser
     *
     * @var \Magento\Tools\Dependency\ParserInterface
     */
    private static $frameworkDependenciesParser;

    /**
     * Modules dependencies report builder
     *
     * @var \Magento\Tools\Dependency\Report\BuilderInterface
     */
    private static $dependenciesReportBuilder;

    /**
     * Modules circular dependencies report builder
     *
     * @var \Magento\Tools\Dependency\Report\BuilderInterface
     */
    private static $circularDependenciesReportBuilder;

    /**
     * Framework dependencies report builder
     *
     * @var \Magento\Tools\Dependency\Report\BuilderInterface
     */
    private static $frameworkDependenciesReportBuilder;

    /**
     * Csv file writer
     *
     * @var \Magento\Framework\File\Csv
     */
    private static $csvWriter;

    /**
     * Get modules dependencies report builder
     *
     * @return \Magento\Tools\Dependency\Report\BuilderInterface
     */
    public static function getDependenciesReportBuilder()
    {
        if (null === self::$dependenciesReportBuilder) {
            self::$dependenciesReportBuilder = new Dependency\Builder(
                self::getComposerJsonParser(),
                new Dependency\Writer(self::getCsvWriter())
            );
        }
        return self::$dependenciesReportBuilder;
    }

    /**
     * Get modules circular dependencies report builder
     *
     * @return \Magento\Tools\Dependency\Report\BuilderInterface
     */
    public static function getCircularDependenciesReportBuilder()
    {
        if (null === self::$circularDependenciesReportBuilder) {
            self::$circularDependenciesReportBuilder = new CircularReport\Builder(
                self::getComposerJsonParser(),
                new CircularReport\Writer(self::getCsvWriter()),
                new CircularTool([], null)
            );
        }
        return self::$circularDependenciesReportBuilder;
    }

    /**
     * Get framework dependencies report builder
     *
     * @return \Magento\Tools\Dependency\Report\BuilderInterface
     */
    public static function getFrameworkDependenciesReportBuilder()
    {
        if (null === self::$frameworkDependenciesReportBuilder) {
            self::$frameworkDependenciesReportBuilder = new Framework\Builder(
                self::getFrameworkDependenciesParser(),
                new Framework\Writer(self::getCsvWriter()),
                self::getXmlConfigParser()
            );
        }
        return self::$frameworkDependenciesReportBuilder;
    }

    /**
     * Get modules dependencies parser
     *
     * @return \Magento\Tools\Dependency\ParserInterface
     */
    private static function getXmlConfigParser()
    {
        if (null === self::$xmlConfigParser) {
            self::$xmlConfigParser = new Parser\Config\Xml();
        }
        return self::$xmlConfigParser;
    }

    /**
     * Get modules dependencies from composer.json parser
     *
     * @return \Magento\Tools\Dependency\ParserInterface
     */
    private static function getComposerJsonParser()
    {
        if (null === self::$composerJsonParser) {
            self::$composerJsonParser = new Parser\Composer\Json();
        }
        return self::$composerJsonParser;
    }

    /**
     * Get framework dependencies parser
     *
     * @return \Magento\Tools\Dependency\ParserInterface
     */
    private static function getFrameworkDependenciesParser()
    {
        if (null === self::$frameworkDependenciesParser) {
            self::$frameworkDependenciesParser = new Parser\Code();
        }
        return self::$frameworkDependenciesParser;
    }

    /**
     * Get csv file writer
     *
     * @return \Magento\Framework\File\Csv
     */
    private static function getCsvWriter()
    {
        if (null === self::$csvWriter) {
            self::$csvWriter = (new Csv())->setDelimiter(';');
        }
        return self::$csvWriter;
    }
}
