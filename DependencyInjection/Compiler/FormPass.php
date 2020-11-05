<?php


namespace  Iphp\FileStoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class FormPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('templating.engines') &&
            in_array('twig', $container->getParameter('templating.engines'))
        ) {
            $twigFormResources = $container->hasParameter('twig.form.resources')
                ? $container->getParameter('twig.form.resources')
                : [];

            $container->setParameter(
                'twig.form.resources',
                array_merge($twigFormResources, ['IphpFileStoreBundle:Form:fields.html.twig'])
            );
        }
    }
}
