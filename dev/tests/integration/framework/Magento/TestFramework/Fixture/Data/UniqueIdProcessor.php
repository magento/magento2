<?php


namespace Magento\TestFramework\Fixture\Data;


class UniqueIdProcessor implements ProcessorInterface
{
    const UNIQUE_ID_KEY = '%uniqid%';
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
     * @param $fixture
     * @return array
     */
    public function process(array &$data, $fixture)
    {
        $class = get_class($fixture);
        if (!isset($this->uniqueId[$class])) {
            $this->uniqueId[$class] = self::INCREMENT;
        }
        $increment = $this->uniqueId[$class]++;
        array_walk_recursive($data, function (&$value) use ($increment) {
            $value = str_replace(self::UNIQUE_ID_KEY, $increment, $value);
        });
        return $data;
    }

    /**
     * @param RevertibleDataFixture $fixture
     */
    public function revert($fixture)
    {
        $class = get_class($fixture);

        if (!isset($this->uniqueId[$class])) {
            return;
        }

        $this->uniqueId[$class]--;

        if ($this->uniqueId[$class] <= 1) {
            unset ($this->uniqueId[$class]);
        }
    }
}
