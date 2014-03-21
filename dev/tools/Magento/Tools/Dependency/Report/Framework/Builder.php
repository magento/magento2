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
namespace Magento\Tools\Dependency\Report\Framework;

use Magento\Tools\Dependency\ParserInterface;
use Magento\Tools\Dependency\Report\Builder\AbstractBuilder;
use Magento\Tools\Dependency\Report\WriterInterface;

/**
 *  Framework dependencies report builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Config parser
     *
     * @var \Magento\Tools\Dependency\ParserInterface
     */
    protected $configParser;

    /**
     * Builder constructor
     *
     * @param ParserInterface $dependenciesParser
     * @param WriterInterface $reportWriter
     * @param ParserInterface $configParser
     */
    public function __construct(
        ParserInterface $dependenciesParser,
        WriterInterface $reportWriter,
        ParserInterface $configParser
    ) {
        parent::__construct($dependenciesParser, $reportWriter);

        $this->configParser = $configParser;
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
        parent::checkOptions($options);

        if (!isset($options['parse']['config_files']) || empty($options['parse']['config_files'])) {
            throw new \InvalidArgumentException('Parse error. Passed option "config_files" is wrong.');
        }
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Tools\Dependency\Report\Framework\Data\Config
     */
    protected function buildData($modulesData)
    {
        $allowedModules = $this->getAllowedModules();

        $modules = array();
        foreach ($modulesData as $moduleData) {
            $dependencies = array();
            foreach ($moduleData['dependencies'] as $dependencyData) {
                if (!in_array($dependencyData['lib'], $allowedModules)) {
                    $dependencies[] = new Data\Dependency($dependencyData['lib'], $dependencyData['count']);
                }
            }
            $modules[] = new Data\Module($moduleData['name'], $dependencies);
        }
        return new Data\Config($modules);
    }

    /**
     * Get allowed modules
     *
     * @return array
     */
    protected function getAllowedModules()
    {
        return array_map(
            function ($element) {
                return $element['name'];
            },
            $this->configParser->parse(array('files_for_parse' => $this->options['parse']['config_files']))
        );
    }
}
