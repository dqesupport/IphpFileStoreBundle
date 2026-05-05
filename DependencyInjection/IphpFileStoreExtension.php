<?php

namespace Iphp\FileStoreBundle\DependencyInjection;

use Iphp\FileStoreBundle\DataStorage\OrmDataStorage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IphpFileStoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Per-driver mapping of (a) doctrine bridge tag to apply on the listener and
     * (b) events to register it for. Modern symfony/doctrine-bridge only processes
     * the `doctrine.event_listener` tag (the legacy `doctrine.event_subscriber`
     * tag is no longer effective), so each event is tagged explicitly.
     *
     * @var array<string, array{tag: string, events: list<string>}>
     */
    protected $tagMap = [
        'orm' => [
            'tag' => 'doctrine.event_listener',
            'events' => ['prePersist', 'postFlush', 'preUpdate', 'postRemove'],
        ],
        // 'document' => ['tag' => '...', 'events' => [...]]
    ];

    /** @var array<string, string> */
    protected $adapterMap = [
        'orm' => OrmDataStorage::class,
        // 'document' => ''
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $driver = strtolower($config['db_driver']);
        if (!isset($this->tagMap[$driver])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid "db_driver" configuration option specified: "%s"',
                $driver
            ));
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('iphp.filestore.mappings', $config['mappings'] ?? []);
        $container->setParameter('iphp.filestore.datastorage.class', $this->adapterMap[$driver]);

        if (!$container->hasParameter('iphp.web_dir')) {
            $container->setParameter('iphp.web_dir', null);
        }

        $listenerDef = $container->getDefinition('iphp.filestore.event_listener.uploader');
        foreach ($this->tagMap[$driver]['events'] as $event) {
            $listenerDef->addTag($this->tagMap[$driver]['tag'], ['event' => $event]);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['TwigBundle'])) {
            return;
        }

        $container->prependExtensionConfig('twig', [
            'form_themes' => ['@IphpFileStore/Form/fields.html.twig'],
        ]);
    }
}
