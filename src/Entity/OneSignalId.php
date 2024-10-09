<?php

namespace App\Entity;

use App\Repository\OneSignalIdRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OneSignalIdRepository::class)]
class OneSignalId
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $oneSignalId = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'oneSignalIds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOneSignalId(): ?string
    {
        return $this->oneSignalId;
    }

    public function setOneSignalId(string $oneSignalId): static
    {
        $this->oneSignalId = $oneSignalId;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}