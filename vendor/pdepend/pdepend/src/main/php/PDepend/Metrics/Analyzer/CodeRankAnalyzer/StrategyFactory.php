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

namespace PDepend\Metrics\Analyzer\CodeRankAnalyzer;

/**
 * Factory for the different code rank strategies.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class StrategyFactory
{
    /**
     * The identifier for the inheritance strategy.
     */
    const STRATEGY_INHERITANCE = 'inheritance';

    /**
     * The identifier for the property strategy.
     */
    const STRATEGY_PROPERTY = 'property';

    /**
     * The identifier for the method strategy.
     */
    const STRATEGY_METHOD = 'method';

    /**
     * The default strategy.
     *
     * @var string
     */
    private $defaultStrategy = self::STRATEGY_INHERITANCE;

    /**
     * List of all valid properties.
     *
     * @var array(string)
     */
    private $validStrategies = array(
        self::STRATEGY_INHERITANCE,
        self::STRATEGY_METHOD,
        self::STRATEGY_PROPERTY
    );

    /**
     * Creates the default code rank strategy.
     *
     * @return \PDepend\Metrics\Analyzer\CodeRankAnalyzer\CodeRankStrategyI
     */
    public function createDefaultStrategy()
    {
        return $this->createStrategy($this->defaultStrategy);
    }

    /**
     * Creates a code rank strategy for the given identifier.
     *
     * @param  string $strategyName The strategy identifier.
     * @return \PDepend\Metrics\Analyzer\CodeRankAnalyzer\CodeRankStrategyI
     * @throws \InvalidArgumentException If the given <b>$id</b> is not valid or
     *                                  no matching class declaration exists.
     */
    public function createStrategy($strategyName)
    {
        if (in_array($strategyName, $this->validStrategies) === false) {
            throw new \InvalidArgumentException(
                sprintf('Cannot load file for identifier "%s".', $strategyName)
            );
        }

        // Prepare identifier
        $name = ucfirst(strtolower($strategyName));

        $className = "PDepend\\Metrics\\Analyzer\\CodeRankAnalyzer\\{$name}Strategy";

        if (false === class_exists($className)) {
            $fileName = "PDepend/Metrics/Analyzer/CodeRankAnalyzer/{$name}Strategy.php";

            include_once $fileName;
        }

        return new $className();
    }
}
