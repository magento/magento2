<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Dependency;

use Magento\Framework\File\Csv;
use Magento\Tools\Dependency\Circular as CircularTool;
use Magento\Tools\Dependency\Parser;
use Magento\Tools\Dependency\Report\Dependency;
use Magento\Tools\Dependency\Report\Circular as CircularReport;
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
                self::getXmlConfigParser(),
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
                self::getXmlConfigParser(),
                new CircularReport\Writer(self::getCsvWriter()),
                new CircularTool(array(), null)
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
