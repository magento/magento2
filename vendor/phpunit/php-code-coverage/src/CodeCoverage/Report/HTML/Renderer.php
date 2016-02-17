<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\Environment\Runtime;

/**
 * Base class for PHP_CodeCoverage_Report_Node renderers.
 *
 * @since Class available since Release 1.1.0
 */
abstract class PHP_CodeCoverage_Report_HTML_Renderer
{
    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var string
     */
    protected $generator;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var int
     */
    protected $lowUpperBound;

    /**
     * @var int
     */
    protected $highLowerBound;

    /**
     * @var string
     */
    protected $version;

    /**
     * Constructor.
     *
     * @param string $templatePath
     * @param string $generator
     * @param string $date
     * @param int    $lowUpperBound
     * @param int    $highLowerBound
     */
    public function __construct($templatePath, $generator, $date, $lowUpperBound, $highLowerBound)
    {
        $version = new SebastianBergmann\Version('2.2.4', dirname(dirname(dirname(dirname(__DIR__)))));

        $this->templatePath   = $templatePath;
        $this->generator      = $generator;
        $this->date           = $date;
        $this->lowUpperBound  = $lowUpperBound;
        $this->highLowerBound = $highLowerBound;
        $this->version        = $version->getVersion();
    }

    /**
     * @param  Text_Template $template
     * @param  array         $data
     * @return string
     */
    protected function renderItemTemplate(Text_Template $template, array $data)
    {
        $numSeparator  = '&nbsp;/&nbsp;';

        if (isset($data['numClasses']) && $data['numClasses'] > 0) {
            $classesLevel = $this->getColorLevel($data['testedClassesPercent']);

            $classesNumber = $data['numTestedClasses'] . $numSeparator .
                $data['numClasses'];

            $classesBar = $this->getCoverageBar(
                $data['testedClassesPercent']
            );
        } else {
            $classesLevel  = 'success';
            $classesNumber = '0' . $numSeparator . '0';
            $classesBar    = $this->getCoverageBar(100);
        }

        if ($data['numMethods'] > 0) {
            $methodsLevel = $this->getColorLevel($data['testedMethodsPercent']);

            $methodsNumber = $data['numTestedMethods'] . $numSeparator .
                $data['numMethods'];

            $methodsBar = $this->getCoverageBar(
                $data['testedMethodsPercent']
            );
        } else {
            $methodsLevel                         = 'success';
            $methodsNumber                        = '0' . $numSeparator . '0';
            $methodsBar                           = $this->getCoverageBar(100);
            $data['testedMethodsPercentAsString'] = '100.00%';
        }

        if ($data['numExecutableLines'] > 0) {
            $linesLevel = $this->getColorLevel($data['linesExecutedPercent']);

            $linesNumber = $data['numExecutedLines'] . $numSeparator .
                $data['numExecutableLines'];

            $linesBar = $this->getCoverageBar(
                $data['linesExecutedPercent']
            );
        } else {
            $linesLevel                           = 'success';
            $linesNumber                          = '0' . $numSeparator . '0';
            $linesBar                             = $this->getCoverageBar(100);
            $data['linesExecutedPercentAsString'] = '100.00%';
        }

        $template->setVar(
            array(
                'icon'                   => isset($data['icon']) ? $data['icon'] : '',
                'crap'                   => isset($data['crap']) ? $data['crap'] : '',
                'name'                   => $data['name'],
                'lines_bar'              => $linesBar,
                'lines_executed_percent' => $data['linesExecutedPercentAsString'],
                'lines_level'            => $linesLevel,
                'lines_number'           => $linesNumber,
                'methods_bar'            => $methodsBar,
                'methods_tested_percent' => $data['testedMethodsPercentAsString'],
                'methods_level'          => $methodsLevel,
                'methods_number'         => $methodsNumber,
                'classes_bar'            => $classesBar,
                'classes_tested_percent' => isset($data['testedClassesPercentAsString']) ? $data['testedClassesPercentAsString'] : '',
                'classes_level'          => $classesLevel,
                'classes_number'         => $classesNumber
            )
        );

        return $template->render();
    }

    /**
     * @param Text_Template                $template
     * @param PHP_CodeCoverage_Report_Node $node
     */
    protected function setCommonTemplateVariables(Text_Template $template, PHP_CodeCoverage_Report_Node $node)
    {
        $runtime = new Runtime;

        $template->setVar(
            array(
                'id'               => $node->getId(),
                'full_path'        => $node->getPath(),
                'path_to_root'     => $this->getPathToRoot($node),
                'breadcrumbs'      => $this->getBreadcrumbs($node),
                'date'             => $this->date,
                'version'          => $this->version,
                'runtime_name'     => $runtime->getName(),
                'runtime_version'  => $runtime->getVersion(),
                'runtime_link'     => $runtime->getVendorUrl(),
                'generator'        => $this->generator,
                'low_upper_bound'  => $this->lowUpperBound,
                'high_lower_bound' => $this->highLowerBound
            )
        );
    }

    protected function getBreadcrumbs(PHP_CodeCoverage_Report_Node $node)
    {
        $breadcrumbs = '';
        $path        = $node->getPathAsArray();
        $pathToRoot  = array();
        $max         = count($path);

        if ($node instanceof PHP_CodeCoverage_Report_Node_File) {
            $max--;
        }

        for ($i = 0; $i < $max; $i++) {
            $pathToRoot[] = str_repeat('../', $i);
        }

        foreach ($path as $step) {
            if ($step !== $node) {
                $breadcrumbs .= $this->getInactiveBreadcrumb(
                    $step,
                    array_pop($pathToRoot)
                );
            } else {
                $breadcrumbs .= $this->getActiveBreadcrumb($step);
            }
        }

        return $breadcrumbs;
    }

    protected function getActiveBreadcrumb(PHP_CodeCoverage_Report_Node $node)
    {
        $buffer = sprintf(
            '        <li class="active">%s</li>' . "\n",
            $node->getName()
        );

        if ($node instanceof PHP_CodeCoverage_Report_Node_Directory) {
            $buffer .= '        <li>(<a href="dashboard.html">Dashboard</a>)</li>' . "\n";
        }

        return $buffer;
    }

    protected function getInactiveBreadcrumb(PHP_CodeCoverage_Report_Node $node, $pathToRoot)
    {
        return sprintf(
            '        <li><a href="%sindex.html">%s</a></li>' . "\n",
            $pathToRoot,
            $node->getName()
        );
    }

    protected function getPathToRoot(PHP_CodeCoverage_Report_Node $node)
    {
        $id    = $node->getId();
        $depth = substr_count($id, '/');

        if ($id != 'index' &&
            $node instanceof PHP_CodeCoverage_Report_Node_Directory) {
            $depth++;
        }

        return str_repeat('../', $depth);
    }

    protected function getCoverageBar($percent)
    {
        $level = $this->getColorLevel($percent);

        $template = new Text_Template(
            $this->templatePath . 'coverage_bar.html',
            '{{',
            '}}'
        );

        $template->setVar(array('level' => $level, 'percent' => sprintf('%.2F', $percent)));

        return $template->render();
    }

    /**
     * @param  int    $percent
     * @return string
     */
    protected function getColorLevel($percent)
    {
        if ($percent <= $this->lowUpperBound) {
            return 'danger';
        } elseif ($percent > $this->lowUpperBound &&
            $percent <  $this->highLowerBound) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}
