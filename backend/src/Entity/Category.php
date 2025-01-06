<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\CategoryRepository;
use App\State\CategoryGetProvider;
use App\State\CategoryPostProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Reference;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ApiResource]
#[Post(
    processor: CategoryPostProcessor::class,
    security: "is_granted('ROLE_USER')"
)]
#[GetCollection(
    provider: CategoryGetProvider::class,
    security: "is_granted('ROLE_USER')"
)]
#[Patch(
    security: "object.getCreator() === user",
    uriTemplate: "/category/{id}"
)]
#[Delete(
    security: "is_granted('ROLE_USER') and object.getCreator() === user",
    uriTemplate: "/category/{id}"
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, RevisionSheet>
     */
    #[ORM\ManyToMany(targetEntity: RevisionSheet::class, mappedBy: 'categories')]
    private Collection $revisionSheets;


    public function __construct()
    {
        $this->revisionSheets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, RevisionSheet>
     */
    public function getRevisionSheets(): Collection
    {
        return $this->revisionSheets;
    }

    public function addRevisionSheet(RevisionSheet $revisionSheet): static
    {
        if (!$this->revisionSheets->contains($revisionSheet)) {
            $this->revisionSheets->add($revisionSheet);
            $revisionSheet->addCategory($this);
        }

        return $this;
    }

    public function removeRevisionSheet(RevisionSheet $revisionSheet): static
    {
        if ($this->revisionSheets->removeElement($revisionSheet)) {
            $revisionSheet->removeCategory($this);
        }

        return $this;
    }
}
