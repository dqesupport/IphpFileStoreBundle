<?php
namespace Iphp\FileStoreBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class BaseTestCase extends WebTestCase
{
    protected $testCaseUniqId;


    protected static function createKernel(array $options = []): KernelInterface
    {
        return self::$kernel = new AppKernel(
            $options['config'] ?? 'default.yml',
            static::getTestEnvFromCalledClass(),
        );
    }

    static function getTestEnvFromCalledClass()
    {
        $class = explode('\\', get_called_class());
        return end($class);

    }
    protected function setUp(): void
    {
        $dir = AppKernel::getTestBaseDir().'/'.static::getTestEnvFromCalledClass();
        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected function getKernel()
    {
        return self::$kernel;
    }

    protected function getEntityManager()
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }


    protected final function importDatabaseSchema()
    {

        $em = $this->getEntityManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);
        }
    }
}
