<?php

namespace App\Entity;

use App\Repository\PiluleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PiluleRepository::class)]
class Pilule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureDePrise = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $tempsMaxi = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $calendrier = null;

    #[ORM\OneToOne(mappedBy: 'pilule', cascade: ['persist', 'remove'])]
    private ?Utilisateur $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getHeureDePrise(): ?\DateTimeInterface
    {
        return $this->heureDePrise;
    }

    public function setHeureDePrise(\DateTimeInterface $heureDePrise): static
    {
        $this->heureDePrise = $heureDePrise;

        return $this;
    }

    public function getTempsMaxi(): ?\DateTimeInterface
    {
        return $this->tempsMaxi;
    }

    public function setTempsMaxi(\DateTimeInterface $tempsMaxi): static
    {
        $this->tempsMaxi = $tempsMaxi;

        return $this;
    }

    public function getCalendrier(): ?string
    {
        return $this->calendrier;
    }

    public function setCalendrier(?string $calendrier): static
    {
        $this->calendrier = $calendrier;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        // unset the owning side of the relation if necessary
        if ($utilisateur === null && $this->utilisateur !== null) {
            $this->utilisateur->setPilule(null);
        }

        // set the owning side of the relation if necessary
        if ($utilisateur !== null && $utilisateur->getPilule() !== $this) {
            $utilisateur->setPilule($this);
        }

        $this->utilisateur = $utilisateur;

        return $this;
    }
}
