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

namespace PDepend\Report;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This factory creates singleton instances of available loggers.
 *
 * The identifiers are used to find a matching service in the DIC
 * that is tagged with 'pdepend.logger' and has an option attribute
 * named after the identifier, prefixed with --:
 *
 * Identifier "my-custom-logger" searchs for:
 *
 *  <tag name="pdepend.logger" option="--my-custom-logger" />
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ReportGeneratorFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates a new generator or returns an existing instance for the given
     * <b>$identifier</b>.
     *
     * @param  string $identifier The generator identifier.
     * @param  string $fileName   The log output file name.
     * @return \PDepend\Report\ReportGenerator
     * @throws \RuntimeException
     */
    public function createGenerator($identifier, $fileName)
    {
        if (!isset($this->instances[$identifier])) {
            $loggerServices = $this->container->findTaggedServiceIds('pdepend.logger');

            $logger = null;

            foreach ($loggerServices as $id => $loggerServiceTags) {
                foreach ($loggerServiceTags as $loggerServiceTag) {
                    if ($loggerServiceTag['option'] === '--' . $identifier) {
                        $logger = $this->container->get($id);
                    }
                }
            }

            if (!$logger) {
                throw new \RuntimeException(sprintf('Unknown generator with identifier "%s".', $identifier));
            }

            // TODO: Refactor this into an external log configurator or a similar
            //       concept.
            if ($logger instanceof FileAwareGenerator) {
                $logger->setLogFile($fileName);
            }

            $this->instances[$identifier] = $logger;
        }
        return $this->instances[$identifier];
    }

    /**
     * Set of created logger instances.
     *
     * @var \PDepend\Report\ReportGenerator[]
     */
    protected $instances = array();
}
