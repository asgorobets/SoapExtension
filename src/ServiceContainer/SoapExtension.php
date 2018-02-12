<?php
/**
 * @author Alexei Gorobet, <asgorobets@gmail.com>
 */
namespace Behat\SoapExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class SoapExtension.
 *
 * @package Behat\SoapExtension\ServiceContainer
 */
class SoapExtension implements Extension
{
    const SOAP_ID = 'soap.extension';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'soap';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadContextInitializer($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $config = $builder->children();

        foreach (['options', 'namespaces'] as $param) {
            $config->arrayNode($param)
                ->defaultValue([])
                ->prototype('scalar')
                ->end();
        }

        $config->end();
    }

    /**
     * Loads context initializer into given Container.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function loadContextInitializer(ContainerBuilder $container, $config)
    {
        $definition = new Definition('Behat\SoapExtension\Context\SoapContextInitializer', array($config));
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition('soap.context_initializer', $definition);
    }
}
