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
namespace Magento\Tools\Dependency\Report\Circular;

use Magento\Tools\Dependency\Circular;
use Magento\Tools\Dependency\ParserInterface;
use Magento\Tools\Dependency\Report\Builder\AbstractBuilder;
use Magento\Tools\Dependency\Report\WriterInterface;

/**
 *  Dependencies report builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Circular dependencies builder
     *
     * @var \Magento\Tools\Dependency\Circular
     */
    protected $circularBuilder;

    /**
     * Builder constructor
     *
     * @param \Magento\Tools\Dependency\ParserInterface $dependenciesParser
     * @param \Magento\Tools\Dependency\Report\WriterInterface $reportWriter
     * @param \Magento\Tools\Dependency\Circular $circularBuilder
     */
    public function __construct(
        ParserInterface $dependenciesParser,
        WriterInterface $reportWriter,
        Circular $circularBuilder
    ) {
        parent::__construct($dependenciesParser, $reportWriter);

        $this->circularBuilder = $circularBuilder;
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Tools\Dependency\Report\Circular\Data\Config
     */
    protected function buildData($modulesData)
    {
        $modules = array();
        foreach ($this->buildCircularDependencies($modulesData) as $moduleName => $modulesChains) {
            $chains = array();
            foreach ($modulesChains as $modulesChain) {
                $chains[] = new Data\Chain($modulesChain);
            }
            $modules[] = new Data\Module($moduleName, $chains);
        }
        return new Data\Config($modules);
    }

    /**
     * Build circular dependencies data by dependencies data
     *
     * @param array $modulesData
     * @return array
     */
    protected function buildCircularDependencies($modulesData)
    {
        $dependencies = array();
        foreach ($modulesData as $moduleData) {
            foreach ($moduleData['dependencies'] as $dependencyData) {
                $dependencies[$moduleData['name']][] = $dependencyData['module'];
            }
        }
        return $this->circularBuilder->buildCircularDependencies($dependencies);
    }
}
