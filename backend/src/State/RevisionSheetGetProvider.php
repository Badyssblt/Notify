<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\RevisionSheetRepository;
use Symfony\Bundle\SecurityBundle\Security;

class RevisionSheetGetProvider implements ProviderInterface
{
    public function __construct(
        private RevisionSheetRepository $repository,
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();

        $revisions = $this->repository->findBy(['owner' => $user]);

        return $revisions;
    }
}
