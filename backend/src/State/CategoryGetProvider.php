<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CategoryRepository;
use App\Repository\RevisionSheetRepository;
use Symfony\Bundle\SecurityBundle\Security;

class CategoryGetProvider implements ProviderInterface
{
    public function __construct(
        private CategoryRepository $repository,
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();

        $categories = $this->repository->findBy(['creator' => $user]);

        return $categories;
    }
}
