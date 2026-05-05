<?php

namespace Iphp\FileStoreBundle\DependencyInjection;

use Iphp\FileStoreBundle\DataStorage\OrmDataStorage;
use Iphp\FileStoreBundle\EventListener\UploaderListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

class IphpFileStoreExtension extends Extension implements PrependExtensionInterface
{
    /** @var array<string, string> */
    protected $tagMap = [
        'orm' => 'doctrine.event_subscriber',
        // 'document' => ''
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

        if (Kernel::VERSION_ID < 70000) {
            // Symfony 5/6: tag exactly as in the legacy symfony5 branch — single
            // doctrine.event_subscriber tag, doctrine reads getSubscribedEvents() itself.
            $listenerDef->addTag($this->tagMap[$driver]);
        } else {
            // Symfony 7+ removed event_subscriber handling from RegisterEventListenersAndSubscribersPass;
            // fall back to one doctrine.event_listener tag per event (events from getSubscribedEvents()).
            $listenerStub = (new \ReflectionClass(UploaderListener::class))->newInstanceWithoutConstructor();
            foreach ($listenerStub->getSubscribedEvents() as $event) {
                $listenerDef->addTag('doctrine.event_listener', ['event' => $event]);
            }
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
