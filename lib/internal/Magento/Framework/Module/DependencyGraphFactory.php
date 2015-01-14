<?php
namespace Magento\Framework\Module;

class DependencyGraphFactory
{
    const ALIAS_PREFIX = 'magento/module-';

    const KEY_REQUIRE = 'require';

    /**
     * @param array $modules
     *
     * @return \Magento\Framework\Data\Graph
     */
    public function create(array $modules)
    {
        $nodes = [];
        $dependencies = [];

        // build the graph data
        foreach ($modules as $module) {
            $jsonDecoder = new \Magento\Framework\Json\Decoder();
            // workaround: convert Magento_X to X
            $module = substr($module, 8);
            $data = $jsonDecoder->decode(file_get_contents(BP . '/app/code/Magento/' . $module . '/composer.json'));
            $nodes[] = strtolower($module);
            foreach (array_keys($data[self::KEY_REQUIRE]) as $depend) {
                if (strpos($depend, self::ALIAS_PREFIX) === 0) {
                    // workaround: convert composer.json to alias x
                    $depend = substr($depend, strlen(self::ALIAS_PREFIX));
                    $depend = str_replace('-', '', $depend);
                    $dependencies[] = [strtolower($module), $depend];
                }
            }
        }
        $nodes = array_unique($nodes);

        return new \Magento\Framework\Data\Graph($nodes, $dependencies);
    }
}
