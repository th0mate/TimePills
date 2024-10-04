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

    #[ORM\OneToOne(mappedBy: 'pilule', cascade: ['persist', 'remove'])]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'pilules')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Utilisateur $proprietaire = null;


    #[ORM\Column(nullable: true)]
    private ?int $nbPilulesPlaquette = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbJoursPause = null;

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


    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setProprietaire(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getProprietaire(): ?Utilisateur
    {
        return $this->proprietaire;
    }

    public function getNbPilulesPlaquette(): ?int
    {
        return $this->nbPilulesPlaquette;
    }

    public function setNbPilulesPlaquette(?int $nbPilulesPlaquette): static
    {
        $this->nbPilulesPlaquette = $nbPilulesPlaquette;

        return $this;
    }

    public function getNbJoursPause(): ?int
    {
        return $this->nbJoursPause;
    }

    public function setNbJoursPause(?int $nbJoursPause): static
    {
        $this->nbJoursPause = $nbJoursPause;

        return $this;
    }
}
