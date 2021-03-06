<?php

namespace CrossKnowledge\DeviceDetectBundle\Tests\Services;

use CrossKnowledge\DeviceDetectBundle\DependencyInjection\CrossKnowledgeDeviceDetectExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class DeviceDetectTest extends \PHPUnit_Framework_TestCase
{
    protected function createContainer($loadAppFile)
    {
        $extension = new CrossKnowledgeDeviceDetectExtension();
        $container = new ContainerBuilder(new ParameterBag(['kernel.cache_dir' => __DIR__.'/fixtures']));
        $container->registerExtension($extension);

        if ($loadAppFile) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/fixtures'));
            $loader->load('config.yml');
        } else {
            $extension->load([], $container);
        }

        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }

    public function toggleWithAndWithoutAppConfig()
    {
        return [
            [true, 'crossknowledge.example_cache_override'],
            [false, 'crossknowledge.default_cache_manager'],
        ];
    }
    /**
     * @dataProvider toggleWithAndWithoutAppConfig
     */
    public function testCacheManagerDefaultIsOverridable($configLoaded, $expectedServicename)
    {
        $container = $this->createContainer($configLoaded);
        $definition = $container->getDefinition('crossknowledge.device_detect');
        $arguments = $definition->getArguments();

        $this->assertEquals(
            $expectedServicename,
            (string)$arguments[1],
            'Once the option cache_manager is '.($configLoaded ? 'set' : 'not set').', the service must be overriden'
        );
    }

}