<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * PDepend Application
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Application
{
    private $container;

    /**
     * @var string
     */
    private $configurationFile;

    /**
     * @param string $configurationFile
     */
    public function setConfigurationFile($configurationFile)
    {
        if (!file_exists($configurationFile)) {
            throw new \InvalidArgumentException(
                sprintf('The configuration file "%s" doesn\'t exist.', $configurationFile)
            );
        }

        $this->configurationFile = $configurationFile;
    }

    /**
     * @return \PDepend\Util\Configuration
     */
    public function getConfiguration()
    {
        return $this->getContainer()->get('pdepend.configuration');
    }

    /**
     * @return \PDepend\Engine
     */
    public function getEngine()
    {
        return $this->getContainer()->get('pdepend.engine');
    }

    /**
     * @return \PDepend\TextUI\Runner
     */
    public function getRunner()
    {
        return $this->getContainer()->get('pdepend.textui.runner'); // TODO: Use standard name? textui is detail.
    }

    /**
     * @return \PDepend\Report\ReportGeneratorFactory
     */
    public function getReportGeneratorFactory()
    {
        return $this->getContainer()->get('pdepend.report_generator_factory');
    }

    /**
     * @return \PDepend\Metrics\AnalyzerFactory
     */
    public function getAnalyzerFactory()
    {
        return $this->getContainer()->get('pdepend.analyzer_factory');
    }

    private function getContainer()
    {
        if ($this->container === null) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function createContainer()
    {
        $extensions = array(new DependencyInjection\PdependExtension());

        $container = new ContainerBuilder(new ParameterBag(array()));
        $container->prependExtensionConfig('pdepend', array());
        $container->addCompilerPass(new DependencyInjection\Compiler\ProcessListenerPass());

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../../resources'));

        foreach ($extensions as $extension) {
            $container->registerExtension($extension);
        }

        if ($this->configurationFile) {
            $loader->load($this->configurationFile);
        }

        $container->compile();

        return $container;
    }

    /**
     * Returns available logger options and documentation messages.
     *
     * @return array(string => string)
     */
    public function getAvailableLoggerOptions()
    {
        return $this->getAvailableOptionsFor('pdepend.logger');
    }

    /**
     * Returns available analyzer options and documentation messages.
     *
     * @return array(string => string)
     */
    public function getAvailableAnalyzerOptions()
    {
        return $this->getAvailableOptionsFor('pdepend.analyzer');
    }

    /**
     * @return array(string => string)
     */
    private function getAvailableOptionsFor($serviceTag)
    {
        $container = $this->getContainer();

        $loggerServices = $container->findTaggedServiceIds($serviceTag);

        $options = array();

        foreach ($loggerServices as $loggerServiceTags) {
            foreach ($loggerServiceTags as $loggerServiceTag) {
                if (isset($loggerServiceTag['option']) && isset($loggerServiceTag['message'])) {
                    $options[$loggerServiceTag['option']] = array(
                        'message' => $loggerServiceTag['message'],
                        'value' => isset($loggerServiceTag['value']) ? $loggerServiceTag['value'] : 'file'
                    );
                }
            }
        }

        return $options;
    }
}
