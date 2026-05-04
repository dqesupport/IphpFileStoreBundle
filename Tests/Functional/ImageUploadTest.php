<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadTest extends BaseTestCase
{
    public function testImageUpload(): void
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertSame(0, $crawler->filter('div.photo')->count());

        $fileToUpload = new UploadedFile(
            __DIR__ . '/../Fixtures/images/sonata-admin-iphpfile.jpeg',
            'sonata-admin-iphpfile.jpeg',
        );

        $client->submit($crawler->selectButton('Upload')->form(), [
            'title' => 'Some title',
            'photo' => $fileToUpload,
            'date[year]' => '2013',
            'date[month]' => '3',
            'date[day]' => '15',
        ]);

        $crawler = $client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.photo')->count());

        $photos = $this->getEntityManager()->getRepository(Photo::class)->findAll();
        $this->assertCount(1, $photos);
        $photo = $photos[0];
        $this->assertSame('Some title', $photo->getTitle());

        $this->assertSame([
            'fileName' => '/2013/03/sonata-admin-iphpfile.jpeg',
            'originalName' => 'sonata-admin-iphpfile.jpeg',
            'mimeType' => 'application/octet-stream',
            'size' => $fileToUpload->getSize(),
            'path' => '/photo/2013/03/sonata-admin-iphpfile.jpeg',
            'width' => 671,
            'height' => 487,
        ], $photo->getPhoto());
    }
}
