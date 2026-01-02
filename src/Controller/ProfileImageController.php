<?php

namespace App\Controller;

use App\Entity\UserImage;
use App\Repository\UserImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile/images', name: 'profile_images_')]
#[IsGranted('ROLE_USER')]
class ProfileImageController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $userImagesDirectory
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserImageRepository $userImageRepository): Response
    {
        $user = $this->getUser();
        $images = $userImageRepository->findAllByUser($user->getId());
        $profileImage = $userImageRepository->findProfileImageByUser($user->getId());

        return $this->render('profile/images.html.twig', [
            'images' => $images,
            'profileImage' => $profileImage,
        ]);
    }

    #[Route('/api/user-images', name: 'api_user_images', methods: ['GET'])]
    public function getApiUserImages(UserImageRepository $userImageRepository): JsonResponse
    {
        $user = $this->getUser();
        $images = $userImageRepository->findAllByUser($user->getId());

        $data = [];
        foreach ($images as $image) {
            $data[] = [
                'id' => $image->getId(),
                'filename' => $image->getFilename(),
                'isProfile' => $image->isIsProfile(),
                'uploadedAt' => $image->getUploadedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $file = $request->files->get('image');

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = 'profile_' . $user->getId() . '_' . uniqid() . '.' . $file->guessExtension();

            $file->move($this->userImagesDirectory, $newFilename);

            $userImage = new UserImage();
            $userImage->setUser($user);
            $userImage->setFilename($newFilename);
            $userImage->setUploadedAt(new \DateTimeImmutable());

            $em->persist($userImage);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'imageId' => $userImage->getId(),
                'filename' => $newFilename,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/set-profile', name: 'set_profile', methods: ['POST'])]
    public function setProfile(UserImage $userImage, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if ($userImage->getUser() !== $user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Remove profile status from all other images
        foreach ($user->getImages() as $image) {
            $image->setIsProfile(false);
        }

        // Set this image as profile
        $userImage->setIsProfile(true);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete(UserImage $userImage, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if ($userImage->getUser() !== $user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Delete the file
        $filepath = $this->userImagesDirectory . '/' . $userImage->getFilename();
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $em->remove($userImage);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
