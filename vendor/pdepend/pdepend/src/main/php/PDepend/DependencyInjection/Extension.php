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

namespace PDepend\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * PDepend base extension class.
 *
 * Copied from Behat
 *
 * @link   https://github.com/Behat/Behat/blob/3.0/src/Behat/Behat/Extension/Extension.php
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Extension
{
    /**
     * Return name of the extension
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $path = rtrim($this->getServiceDefinitionsPath(), DIRECTORY_SEPARATOR);
        $name = $this->getServiceDefinitionsName();

        if (file_exists($path . DIRECTORY_SEPARATOR . ($file = $name . '.xml'))) {
            $loader = new XmlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }
        if (file_exists($path . DIRECTORY_SEPARATOR . ($file = $name . '.yml'))) {
            $loader = new YamlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }

        $container->setParameter($this->getName() . '.parameters', $config);
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->useAttributeAsKey('name')
            ->prototype('variable');
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return array();
    }

    /**
     * Returns name of the service definition config without extension and path.
     *
     * @return string
     */
    protected function getServiceDefinitionsName()
    {
        return 'services';
    }

    /**
     * Returns service definition configs path.
     *
     * @return string
     */
    protected function getServiceDefinitionsPath()
    {
        $reflection = new ReflectionClass($this);

        return dirname($reflection->getFileName());
    }
}
