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
namespace Magento\Tools\Dependency\Report\Builder;

use Magento\Tools\Dependency\Report\BuilderInterface;
use Magento\Tools\Dependency\ParserInterface;
use Magento\Tools\Dependency\Report\WriterInterface;

/**
 *  Abstract report builder by config files
 */
abstract class AbstractBuilder implements BuilderInterface
{
    /**
     * Dependencies parser
     *
     * @var \Magento\Tools\Dependency\ParserInterface
     */
    protected $dependenciesParser;

    /**
     * Report writer
     *
     * @var \Magento\Tools\Dependency\Report\WriterInterface
     */
    protected $reportWriter;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Builder constructor
     *
     * @param ParserInterface $dependenciesParser
     * @param WriterInterface $reportWriter
     */
    public function __construct(ParserInterface $dependenciesParser, WriterInterface $reportWriter)
    {
        $this->dependenciesParser = $dependenciesParser;
        $this->reportWriter = $reportWriter;
    }

    /**
     * Template method. Main algorithm
     *
     * {@inheritdoc}
     */
    public function build(array $options)
    {
        $this->checkOptions($options);
        $this->options = $options;

        $config = $this->buildData($this->dependenciesParser->parse($options['parse']));
        $this->reportWriter->write($options['write'], $config);
    }

    /**
     * Template method. Check passed options step
     *
     * @param array $options
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function checkOptions($options)
    {
        if (!isset($options['parse']) || empty($options['parse'])) {
            throw new \InvalidArgumentException('Passed option section "parse" is wrong.');
        }

        if (!isset($options['write']) || empty($options['write'])) {
            throw new \InvalidArgumentException('Passed option section "write" is wrong.');
        }
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Tools\Dependency\Report\Data\ConfigInterface
     */
    abstract protected function buildData($modulesData);
}
