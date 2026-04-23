<?php

namespace Magento\Framework\Validator;

/**
 * Factory class for creating instances of the Regex validator.
 *
 * Note: This class is included as a stub in static tests to ensure
 * compatibility with static analysis tools. When running static tests,
 * ensure the corresponding stub is properly placed in the test environment.
 */
class RegexFactory
{
    /**
     * Create a new Regex validator instance.
     *
     * @param array $data Optional configuration data for the Regex validator.
     * @return Regex
     *
     * @note During static tests, this method may be tested using the stubbed
     *       version of this class to avoid dependency injection-related issues.
     */
    public function create(array $data = []): Regex
    {
        return new Regex($data);
    }
}
