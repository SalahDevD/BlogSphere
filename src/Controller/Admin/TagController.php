<?php
namespace App\Controller\Admin;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/tag')]
#[IsGranted('ROLE_ADMIN')]
class TagController extends AbstractController
{
    #[Route('/', name: 'admin_tag_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $tags = $em->getRepository(Tag::class)->findAll();
        
        return $this->render('admin/tags/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/new', name: 'admin_tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            
            $tag = new Tag();
            $tag->setName($name);
            $tag->setSlug($slugger->slug($name)->lower());
            
            $em->persist($tag);
            $em->flush();

            $this->addFlash('success', 'Tag créé avec succès !');
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('admin/tags/new.html.twig');
    }

    #[Route('/{id}/delete', name: 'admin_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('success', 'Tag supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_tag_index');
    }
}
