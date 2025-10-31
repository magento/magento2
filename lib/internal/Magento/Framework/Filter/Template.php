<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter;

use InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\DirectiveProcessor\DependDirective;
use Magento\Framework\Filter\DirectiveProcessor\ForDirective;
use Magento\Framework\Filter\DirectiveProcessor\IfDirective;
use Magento\Framework\Filter\DirectiveProcessor\LegacyDirective;
use Magento\Framework\Filter\DirectiveProcessor\TemplateDirective;
use Magento\Framework\Filter\DirectiveProcessor\VarDirective;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Filter\Template\SignatureProvider;
use Magento\Framework\Filter\Template\FilteringDepthMeter;

/**
 * Template constructions filter
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Template implements FilterInterface
{
    /**
     * Construction regular expression
     *
     * @deprecated Use the new Directive processors
     */
    public const CONSTRUCTION_PATTERN = '/{{([a-z]{0,10})(.*?)}}(?:(.*?)(?:{{\/(?:\\1)}}))?/si';

    /**
     * Construction `depend` regular expression
     *
     * @deprecated Use the new Directive processors
     */
    public const CONSTRUCTION_DEPEND_PATTERN = '/{{depend\s*(.*?)}}(.*?){{\\/depend\s*}}/si';

    /**
     * Construction `if` regular expression
     *
     * @deprecated Use the new Directive processors
     */
    public const CONSTRUCTION_IF_PATTERN = '/{{if\s*(.*?)}}(.*?)({{else}}(.*?))?{{\\/if\s*}}/si';

    /**
     * Construction `template` regular expression
     *
     * @deprecated Use the new Directive processors
     */
    public const CONSTRUCTION_TEMPLATE_PATTERN = '/{{(template)(.*?)}}/si';

    /**
     * Construction `for` regular expression
     *
     * @deprecated Use the new Directive processors
     */
    public const LOOP_PATTERN = '/{{for(?P<loopItem>.*? )(in)(?P<loopData>.*?)}}(?P<loopBody>.*?){{\/for}}/si';

    /**
     * @var array
     */
    private $afterFilterCallbacks = [];

    /**
     * Assigned template variables
     *
     * @var array
     */
    protected $templateVars = [];

    /**
     * @var callable|null
     */
    protected $templateProcessor = null;

    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @var DirectiveProcessorInterface[]
     */
    private $directiveProcessors;

    /**
     * @var bool
     */
    private $strictMode = true;

    /**
     * @var VariableResolverInterface|null
     */
    private $variableResolver;

    /**
     * @var SignatureProvider|null
     */
    private $signatureProvider;

    /**
     * @var FilteringDepthMeter|null
     */
    private $filteringDepthMeter;

    /**
     * @param StringUtils $string
     * @param array $variables
     * @param DirectiveProcessorInterface[] $directiveProcessors
     * @param VariableResolverInterface|null $variableResolver
     * @param SignatureProvider|null $signatureProvider
     * @param FilteringDepthMeter|null $filteringDepthMeter
     */
    public function __construct(
        StringUtils $string,
        $variables = [],
        $directiveProcessors = [],
        ?VariableResolverInterface $variableResolver = null,
        ?SignatureProvider $signatureProvider = null,
        ?FilteringDepthMeter $filteringDepthMeter = null
    ) {
        $this->string = $string;
        $this->setVariables($variables);
        $this->directiveProcessors = $directiveProcessors;
        $this->variableResolver = $variableResolver ?? ObjectManager::getInstance()
                ->get(VariableResolverInterface::class);

        $this->signatureProvider = $signatureProvider ?? ObjectManager::getInstance()
                ->get(SignatureProvider::class);

        $this->filteringDepthMeter = $filteringDepthMeter ?? ObjectManager::getInstance()
                ->get(FilteringDepthMeter::class);

        if (empty($directiveProcessors)) {
            $this->directiveProcessors = [
                'depend' => ObjectManager::getInstance()->get(DependDirective::class),
                'if' => ObjectManager::getInstance()->get(IfDirective::class),
                'template' => ObjectManager::getInstance()->get(TemplateDirective::class),
                'legacy' => ObjectManager::getInstance()->get(LegacyDirective::class),
            ];
        }
    }

    /**
     * Set the template variables available to be resolved in this template via variable resolver directives
     *
     * @param array $variables
     * @return \Magento\Framework\Filter\Template
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->templateVars[$name] = $value;
        }
        return $this;
    }

    /**
     * Sets the processor for template directive.
     *
     * @param callable $callback it must return string
     * @return $this
     */
    public function setTemplateProcessor(callable $callback)
    {
        $this->templateProcessor = $callback;
        return $this;
    }

    /**
     * Sets the processor for template directive.
     *
     * @return callable|null
     */
    public function getTemplateProcessor()
    {
        return is_callable($this->templateProcessor) ? $this->templateProcessor : null;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     *
     * @throws \Exception
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(__(
                'Argument \'value\' must be type of string, %1 given.',
                gettype($value)
            )->render());
        }

        $this->filteringDepthMeter->descend();

        // Processing of template directives.
        $templateDirectivesResults = array_unique(
            $this->processDirectives($value),
            SORT_REGULAR
        );

        $value = $this->applyDirectivesResults($value, $templateDirectivesResults);

        // Processing of deferred directives received from child templates
        // or nested directives.
        $deferredDirectivesResults = array_unique(
            $this->processDirectives($value, true),
            SORT_REGULAR
        );

        $value = $this->applyDirectivesResults($value, $deferredDirectivesResults);

        if ($this->filteringDepthMeter->showMark() > 1) {
            // Signing own deferred directives (if any).
            $signature = $this->signatureProvider->get();

            foreach ($templateDirectivesResults as $result) {
                if ($result['directive'] === $result['output']) {
                    $value = str_replace(
                        $result['output'],
                        $signature . $result['output'] . $signature,
                        $value
                    );
                }
            }
        }

        $value = $this->afterFilter($value);

        $this->filteringDepthMeter->ascend();

        return $value;
    }

    /**
     * Processes template directives and returns an array that contains results produced by each directive.
     *
     * @param string $value
     * @param bool $isSigned
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processDirectives($value, $isSigned = false): array
    {
        $results = [];

        foreach ($this->directiveProcessors as $directiveProcessor) {
            if (!$directiveProcessor instanceof DirectiveProcessorInterface) {
                throw new InvalidArgumentException(
                    'Directive processors must implement ' . DirectiveProcessorInterface::class
                );
            }

            $pattern = $directiveProcessor->getRegularExpression();

            if ($isSigned) {
                $pattern = $this->embedSignatureIntoPattern($pattern);
            }

            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $replacedValue = $directiveProcessor->process($construction, $this, $this->templateVars);

                    $result = [
                        'directive' => $construction[0],
                        'output' => $replacedValue
                    ];

                    if (count($this->afterFilterCallbacks) > 0) {
                        $result['callbacks'] = $this->afterFilterCallbacks;

                        $this->resetAfterFilterCallbacks();
                    }

                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    /**
     * Applies results produced by directives.
     *
     * @param string $value
     * @param array $results
     *
     * @return string
     */
    private function applyDirectivesResults(string $value, array $results): string
    {
        $processedResults = [];

        foreach ($results as $result) {
            foreach ($processedResults as $processedResult) {
                $result['directive'] = str_replace(
                    $processedResult['directive'],
                    $processedResult['output'],
                    $result['directive']
                );
            }

            $value = str_replace($result['directive'], $result['output'], $value);

            if (isset($result['callbacks'])) {
                foreach ($result['callbacks'] as $callback) {
                    $this->addAfterFilterCallback($callback);
                }
            }

            $processedResults[] = $result;
        }

        return $value;
    }

    /**
     * Modifies given regular expression pattern to be able to recognize signed directives.
     *
     * @param string $pattern
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function embedSignatureIntoPattern(string $pattern): string
    {
        $signature = $this->signatureProvider->get();

        $closingDelimiters = [
            '(' => ')',
            '{' => '}',
            '[' => ']',
            '<' => '>'
        ];

        $closingDelimiter = $openingDelimiter = substr(trim($pattern), 0, 1);

        if (array_key_exists($openingDelimiter, $closingDelimiters)) {
            $closingDelimiter = $closingDelimiters[$openingDelimiter];
        }

        $pattern = substr_replace($pattern, $signature, strpos($pattern, $openingDelimiter) + 1, 0);
        $pattern = substr_replace($pattern, $signature, strrpos($pattern, $closingDelimiter), 0);

        return $pattern;
    }

    /**
     * Runs callbacks that have been added to filter content after directive processing is finished.
     *
     * @param string $value
     * @return string
     */
    protected function afterFilter($value)
    {
        foreach ($this->afterFilterCallbacks as $callback) {
            $value = call_user_func($callback, $value);
        }
        // Since a single instance of this class can be used to filter content multiple times, reset callbacks to
        // prevent callbacks running for unrelated content (e.g., email subject and email body)
        $this->resetAfterFilterCallbacks();
        return $value;
    }

    /**
     * Adds a callback to run after main filtering has happened.
     *
     * Callback must accept a single argument and return a string of the processed value.
     *
     * @param callable $afterFilterCallback
     * @return $this
     */
    public function addAfterFilterCallback(callable $afterFilterCallback)
    {
        // Only add callback if it doesn't already exist
        if (in_array($afterFilterCallback, $this->afterFilterCallbacks)) {
            return $this;
        }

        $this->afterFilterCallbacks[] = $afterFilterCallback;
        return $this;
    }

    /**
     * Resets the after filter callbacks
     *
     * @return $this
     */
    protected function resetAfterFilterCallbacks()
    {
        $this->afterFilterCallbacks = [];
        return $this;
    }

    /**
     * Process {{var}} directive regex match
     *
     * @param string[] $construction
     * @return string
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    public function varDirective($construction)
    {
        $directive = $this->directiveProcessors['var'] ?? ObjectManager::getInstance()
            ->get(VarDirective::class);

        return $directive->process($construction, $this, $this->templateVars);
    }

    /**
     * Process {{for}} directive regex match
     *
     * @param string[] $construction
     * @return string
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    public function forDirective($construction)
    {
        $directive = $this->directiveProcessors['for'] ?? ObjectManager::getInstance()
            ->get(ForDirective::class);

        preg_match($directive->getRegularExpression(), $construction[0] ?? '', $specificConstruction);

        return $directive->process($specificConstruction, $this, $this->templateVars);
    }

    /**
     * Allows templates to be included inside other templates
     *
     * Usage:
     *
     *     {{template config_path="<PATH>"}}
     *
     * <PATH> equals the XPATH to the system configuration value that contains the value of the template.
     * This directive is useful to include things like a global header/footer.
     *
     * @param string[] $construction
     * @return mixed
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    public function templateDirective($construction)
    {
        $directive = $this->directiveProcessors['template'] ?? ObjectManager::getInstance()
            ->get(TemplateDirective::class);

        return $directive->process($construction, $this, $this->templateVars);
    }

    /**
     * Get depend directive
     *
     * @param string[] $construction
     * @return string
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    public function dependDirective($construction)
    {
        $directive = $this->directiveProcessors['depend'] ?? ObjectManager::getInstance()
            ->get(DependDirective::class);

        preg_match($directive->getRegularExpression(), $construction[0] ?? '', $specificConstruction);

        return $directive->process($specificConstruction, $this, $this->templateVars);
    }

    /**
     * If directive
     *
     * @param string[] $construction
     * @return string
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    public function ifDirective($construction)
    {
        $directive = $this->directiveProcessors['if'] ?? ObjectManager::getInstance()
            ->get(IfDirective::class);

        preg_match($directive->getRegularExpression(), $construction[0] ?? '', $specificConstruction);

        return $directive->process($specificConstruction, $this, $this->templateVars);
    }

    /**
     * Return associative array of parameters.
     *
     * @param string $value raw parameters
     * @return array
     * @deprecated 102.0.4 Use the directive interfaces instead
     * @see \Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface
     */
    protected function getParameters($value)
    {
        $tokenizer = new Template\Tokenizer\Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        foreach ($params as $key => $value) {
            if ($value !== null && substr($value, 0, 1) === '$') {
                $params[$key] = $this->getVariable(substr($value, 1), null);
            }
        }
        return $params;
    }

    /**
     * Resolve a variable's value for a given var directive construction
     *
     * @param string $value raw parameters
     * @param string $default default value
     * @return string
     * @deprecated 102.0.4 Use \Magento\Framework\Filter\VariableResolverInterface instead
     * @see \Magento\Framework\Filter\VariableResolverInterface
     */
    protected function getVariable($value, $default = '{no_value_defined}')
    {
        \Magento\Framework\Profiler::start('email_template_processing_variables');
        $result = $this->variableResolver->resolve($value, $this, $this->templateVars) ?? $default;
        \Magento\Framework\Profiler::stop('email_template_processing_variables');

        return $result;
    }

    /**
     * Loops over a set of stack args to process variables into array argument values
     *
     * @param array $stack
     * @return array
     * @deprecated 102.0.4 Use new directive processor interfaces
     * @see \Magento\Framework\Filter\DirectiveProcessorInterface
     */
    protected function getStackArgs($stack)
    {
        foreach ($stack as $i => $value) {
            if (is_array($value)) {
                $stack[$i] = $this->getStackArgs($value);
            } elseif ($value !== null && substr($value, 0, 1) === '$') {
                $stack[$i] = $this->getVariable(substr($value, 1), null);
            }
        }
        return $stack;
    }

    /**
     * Change the operating mode for filtering and return the previous mode
     *
     * Returning the previous value makes it easy to perform single operations in a single mode:
     *
     * <code>
     * $previousMode = $filter->setStrictMode(true);
     * $filter->filter($value);
     * $filter->setStrictMode($previousMode);
     * </code>
     *
     * @param bool $strictMode Enable strict parsing of directives
     * @return bool The previous mode from before the change
     * @since 102.0.4
     * @deprecated The method is not in use anymore.
     * @see no alternatives
     */
    public function setStrictMode(bool $strictMode): bool
    {
        $current = $this->strictMode;
        $this->strictMode = $strictMode;

        return $current;
    }

    /**
     * Return if the template is rendered with strict directive processing
     *
     * @return bool
     * @since 102.0.4
     * @deprecated
     * @see no alternatives
     */
    public function isStrictMode(): bool
    {
        return $this->strictMode;
    }
}
