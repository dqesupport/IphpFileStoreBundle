<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Iphp\FileStoreBundle\Form\Type\FileType as IphpFileType;
use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function index(Request $request, FormFactoryInterface $formFactory): Response
    {
        $photo = new Photo();

        $uploadForm = $formFactory->createNamedBuilder('', FormType::class, $photo)
            ->add('title', TextType::class)
            ->add('date', DateType::class, ['widget' => 'choice', 'years' => range(2010, 2030)])
            ->add('photo', FileType::class)
            ->add('photoUpload', FileType::class)
            ->getForm();

        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            return $this->redirectToRoute('photo_index');
        }

        return $this->render('@Test/Photo/index.html.twig', [
            'uploadForm' => $uploadForm->createView(),
            'photos' => $this->em->getRepository(Photo::class)->findAll(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $photo = $this->em->getRepository(Photo::class)->find($id);

        $editForm = $this->createFormBuilder($photo)
            ->add('title', TextType::class)
            ->add('date', DateType::class, ['widget' => 'choice', 'years' => range(2010, 2030)])
            ->add('photo', IphpFileType::class)
            ->add('photoUpload', FileType::class)
            ->add('photoInfo', IphpFileType::class)
            ->getForm();

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            return $this->redirectToRoute('photo_edit', ['id' => $photo->getId()]);
        }

        return $this->render('@Test/Photo/edit.html.twig', [
            'photo' => $photo,
            'editForm' => $editForm->createView(),
        ]);
    }
}
