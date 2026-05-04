<?php

namespace Iphp\FileStoreBundle\DependencyInjection;

use Iphp\FileStoreBundle\DataStorage\OrmDataStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class IphpFileStoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('iphp.filestore.mappings', $config['mappings'] ?? []);
        $container->setParameter('iphp.filestore.datastorage.class', OrmDataStorage::class);
    }
}
