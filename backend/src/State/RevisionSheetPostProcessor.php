<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\RevisionSheet;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RevisionSheetPostProcessor implements ProcessorInterface

{
    public function __construct(private Security $security,
                                #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
                                private ProcessorInterface $processor)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if($data instanceof RevisionSheet){
            $user = $this->security->getUser();
            $data->setOwner($user);


        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
