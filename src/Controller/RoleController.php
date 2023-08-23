<?php

namespace App\Controller;

use App\Service\MSGraphService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/role')]
class RoleController extends AbstractController
{
    public function __construct(private readonly MSGraphService $graphService)
    { }

    #[Route('/assign/{uuid}/{role}', name: 'assign_role_user', methods: ['POST'])]
    public function assignRole($uuid ,$role):JsonResponse
    {
        $this->graphService->deleteUserRole($uuid);
        return $this->json($this->graphService->addUserRole($uuid,$role));
    }
}
