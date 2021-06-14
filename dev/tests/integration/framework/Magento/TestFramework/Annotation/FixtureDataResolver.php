<?php


namespace Magento\TestFramework\Annotation;

class FixtureDataResolver
{
    /**
     * Fixture's name storage
     * @var array
     */
    private $uniqueId = [];

    /**
     * Fixture  starting increment number
     * @var int
     */
    private const INCREMENT = 1;

    /**
     * @param array $data
     * @param RevertibleDataFixture $class
     * @return array
     * @throws \ReflectionException
     */
    public function resolveDataReferences(array $data, $class)
    {
        $reflection = (new \ReflectionClass($class));

        if (!isset($this->uniqueId[$reflection->getName()])) {
            $this->uniqueId[$reflection->getName()] = self::INCREMENT;
            return $data;
        }

        $increment = ++$this->uniqueId[$reflection->getName()];

        $uniqueFields = explode('@unique', $reflection->getDocComment());
        array_shift($uniqueFields);
        array_walk($uniqueFields, function(&$val) {
            $val= preg_replace('/[^a-z_]/i','',$val);
        });
        $sequence = '_' . $increment;

        foreach ($data as $key => $value) {
            if (in_array($key, $uniqueFields)) {
                $data[$key] = $value . $sequence;
            }

        }

        return $data;
    }

    /**
     * @param RevertibleDataFixture $class
     * @throws \ReflectionException
     */
    public function revert($class)
    {
        $reflection = (new \ReflectionClass($class));

        if (!isset($this->uniqueId[$reflection->getName()])) {
            return;
        }

        $this->uniqueId[$reflection->getName()]--;

        if ($this->uniqueId[$reflection->getName()] <= 1) {
            unset ($this->uniqueId[$reflection->getName()]);
        }
    }
}
