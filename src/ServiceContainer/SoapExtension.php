<?php
/**
 * @author Alexei Gorobet, <asgorobets@gmail.com>
 */
namespace Behat\SoapExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;

/**
 * Class SoapExtension.
 *
 * @package Behat\SoapExtension\ServiceContainer
 */
class SoapExtension implements Extension
{
    /**
     * @var ServiceProcessor
     */
    private $processor;
    /**
     * Extension namespace.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * @param ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
        // Remove "ServiceContainer" from the namespace.
        $this->namespace = implode('\\', array_slice(explode('\\', __NAMESPACE__), 0, -1));
    }

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
        $this->setDefinition($container, 'SoapContextInitializer', ContextExtension::INITIALIZER_TAG, [$config]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->setDefinition($container, 'SoapContextReader', EnvironmentExtension::READER_TAG . '.context', [
          $this->processor->findAndSortTaggedServices($container, ContextExtension::READER_TAG),
        ]);
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
     * Add definition to DI container.
     *
     * @param ContainerBuilder $container
     *   DI container.
     * @param string $class
     *   Class name (in SoapExtension namespace).
     * @param string $id
     *   Definition ID.
     * @param array $arguments
     *   Definition arguments.
     */
    private function setDefinition(ContainerBuilder $container, $class, $id, array $arguments = [])
    {
        $definition = new Definition("$this->namespace\\Context\\$class", $arguments);
        $definition->addTag($id, ['priority' => 0]);
        $container->setDefinition($id, $definition);
    }
}
