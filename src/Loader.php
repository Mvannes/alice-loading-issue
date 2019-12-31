<?php
namespace VanNes;

use Nelmio\Alice\Loader\NativeLoader;

class Loader
{
    private $loader;

    public function __construct()
    {
        $this->loader = new NativeLoader();
    }

    public function doLoad(): void
    {
        $objectSet = $this->loader->loadFile(__DIR__ . '/Resources/broken.yaml');
        var_dump($objectSet->getObjects());
    }
}
