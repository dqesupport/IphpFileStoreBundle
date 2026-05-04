<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Iphp\FileStoreBundle\IphpFileStoreBundle;
use Iphp\FileStoreBundle\Tests\Functional\TestBundle\TestBundle;
use Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\TestXmlConfigBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    protected string $config;

    protected string $testEnv;

    public function __construct(string $config, string $testEnv = 'default')
    {
        parent::__construct($testEnv . '_' . substr(md5($config), 0, 3), true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__ . '/config/' . $config;
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
        $this->testEnv = $testEnv;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
            new IphpFileStoreBundle(),
            new TestBundle(),
            new TestXmlConfigBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->config);
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        return $this->getTestEnvDir() . '/app/cache/' . substr(md5($this->config), 0, 3);
    }

    public function getBuildDir(): string
    {
        return $this->getCacheDir();
    }

    public function getLogDir(): string
    {
        return $this->getTestEnvDir() . '/app/logs';
    }

    public function getConfig(): string
    {
        return $this->config;
    }

    public static function getTestBaseDir(): string
    {
        return sys_get_temp_dir() . '/IphpFileStoreTestBundle';
    }

    public function getTestEnvDir(): string
    {
        return self::getTestBaseDir() . '/' . $this->testEnv;
    }

    public function makeTestEnvDir(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->getTestEnvDir());
        $fs->mkdir($this->getTestEnvDir());
    }

    protected function getKernelParameters(): array
    {
        return array_merge(parent::getKernelParameters(), [
            'kernel.test_env' => $this->testEnv,
            'kernel.test_env_dir' => $this->getTestEnvDir(),
        ]);
    }

    public function getTestEnv(): string
    {
        return $this->testEnv;
    }
}
