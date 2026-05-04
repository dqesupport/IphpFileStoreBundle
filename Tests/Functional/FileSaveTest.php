<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

use Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity\File as FileEntity;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;

class FileSaveTest extends BaseTestCase
{
    public function testFileSaveUpload(): void
    {
        $this->createClient();
        $this->importDatabaseSchema();

        $file = new FileEntity();
        $existsFile = new File(__DIR__ . '/../Fixtures/files/text.txt');

        $file->setTitle('new file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFile($existsFile);

        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();

        $this->assertSame([
            'fileName' => '/File/file/2013/1.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/File/file/2013/1.txt',
        ], $file->getFile());

        unset($file);
        $this->getEntityManager()->clear();
        static::ensureKernelShutdown();

        $client = $this->createClient(['config' => 'default_newfilepath.yml']);
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $command = $application->find('iphp:filestore:repair');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--entity' => FileEntity::class,
            '--field' => 'file',
            '--force' => 1,
            '--webdir' => realpath(static::getContainer()->getParameter('kernel.test_env_dir') . '/web/'),
        ]);

        $newFile = $this->getEntityManager()->getRepository(FileEntity::class)->findOneBy(['title' => 'new file']);

        $this->assertSame([
            'fileName' => '/1/new-file.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/other/uploads/1/new-file.txt',
        ], $newFile->getFile());

        unset($newFile);
        unset($commandTester);
        unset($command);
        unset($application);
        $this->getEntityManager()->clear();
        static::ensureKernelShutdown();
    }
}
