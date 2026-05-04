<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageEditTest extends BaseTestCase
{
    public function testImageEditAndDelete(): void
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();

        $this->getEntityManager()->persist($this->createTestPhotoObject());
        $this->getEntityManager()->flush();
        $this->assertLoadedPhotoParams($this->getEntityManager()->getRepository(Photo::class)->find(1));

        $crawler = $client->request('GET', '/edit/1/');
        $this->assertPhotoExistsInForm($crawler);

        $newFileToUpload = new UploadedFile(__DIR__ . '/../Fixtures/images/php-elephant.png', 'php-elephant.png');
        $alsoNewFileToUpload = new UploadedFile(__DIR__ . '/../Fixtures/images/github1.png', 'github1.png');

        $form = $crawler->selectButton('Save')->form();
        $form['form[photo][file]']->upload($newFileToUpload);
        $form['form[photoUpload]']->upload($alsoNewFileToUpload);
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPreviousImageGone($crawler);

        $form = $crawler->selectButton('Save')->form();
        $form['form[photo][delete]']->tick();
        $form['form[photoInfo][delete]']->tick();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertNoPhotoOnForm($crawler);

        $this->getEntityManager()->clear();
        $photoAfterUpdate = $this->getEntityManager()->getRepository(Photo::class)->find(1);
        $this->assertNull($photoAfterUpdate->getPhoto());
        $this->assertNull($photoAfterUpdate->getPhotoInfo());
    }

    private function createTestPhotoObject(): Photo
    {
        $existsFile = new File(__DIR__ . '/../Fixtures/images/front-images-list.jpeg');
        $alsoExistsFile = new File(__DIR__ . '/../Fixtures/images/sonata-admin-iphpfile.jpeg');

        $photo = new Photo();
        $photo->setTitle('Second photo')
            ->setDate(new \DateTime('2013-04-05 00:00:00'))
            ->setPhoto($existsFile)
            ->setPhotoUpload($alsoExistsFile);

        return $photo;
    }

    private function assertLoadedPhotoParams(Photo $photoLoaded): void
    {
        $this->assertSame([
            'fileName' => '/2013/04/front-images-list.jpeg',
            'originalName' => 'front-images-list.jpeg',
            'mimeType' => 'image/jpeg',
            'size' => 67521,
            'path' => '/photo/2013/04/front-images-list.jpeg',
            'width' => 445,
            'height' => 531,
        ], $photoLoaded->getPhoto());

        $this->assertSame([
            'fileName' => '/2013/04/sonata-admin-iphpfile.jpeg',
            'originalName' => 'sonata-admin-iphpfile.jpeg',
            'mimeType' => 'image/jpeg',
            'size' => 48332,
            'path' => '/photo/2013/04/sonata-admin-iphpfile.jpeg',
            'width' => 671,
            'height' => 487,
        ], $photoLoaded->getPhotoInfo());
    }

    private function assertPhotoExistsInForm(Crawler $crawler): void
    {
        $this->assertSame(1, $crawler->filter('input[id="form_title"][value="Second photo"]')->count());
        $this->assertSame(1, $crawler->filter('option[value="2013"][selected="selected"]')->count());
        $this->assertSame(1, $crawler->filter('option[value="4"][selected="selected"]')->count());

        $this->assertSame(1, $crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count());
        $this->assertSame(1, $crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count());

        $this->assertSame(1, $crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count());
        $this->assertSame(1, $crawler->filter('input[type="checkbox"][id="form_photoInfo_delete"]')->count());
    }

    private function assertNoPhotoOnForm(Crawler $crawler): void
    {
        $this->assertSame(0, $crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count());
        $this->assertSame(0, $crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count());
        $this->assertSame(0, $crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count());
        $this->assertSame(0, $crawler->filter('input[type="checkbox"][id="form_photoInfo_delete"]')->count());
    }

    private function assertPreviousImageGone(Crawler $crawler): void
    {
        $this->assertSame(0, $crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count());
        $this->assertSame(0, $crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count());
        $this->assertSame(1, $crawler->filter('img[src="/photo/2013/04/php-elephant.png"]')->count());
        $this->assertSame(1, $crawler->filter('img[src="/photo/2013/04/github1.png"]')->count());
    }
}
