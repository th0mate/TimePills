<?php

namespace App\Entity;

use App\Repository\PiluleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PiluleRepository::class)]
class Pilule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Groups('pilule:read')]
    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureDePrise = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $tempsMaxi = null;
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'pilules')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Utilisateur $proprietaire = null;

    #[Groups('pilule:read')]
    #[ORM\Column(nullable: true)]
    private ?int $nbPilulesPlaquette = null;

    #[Groups('pilule:read')]
    #[ORM\Column(nullable: true)]
    private ?int $nbJoursPause = null;

    #[Groups('pilule:read')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDerniereReprise = null;

    #[Groups('pilule:read')]
    #[ORM\OneToMany(mappedBy: 'pilule', targetEntity: DatePrise::class, cascade: ['persist', 'remove'])]
    private Collection $datesPrises;

    public function __construct()
    {
        $this->datesPrises = new ArrayCollection();
    }

    public function getDatesPrises(): Collection
    {
        return $this->datesPrises;
    }

    public function addDatePrise(DatePrise $datePrise): self
    {
        if (!$this->datesPrises->contains($datePrise)) {
            $this->datesPrises[] = $datePrise;
            $datePrise->setPilule($this);
        }

        return $this;
    }

    public function removeDatePrise(DatePrise $datePrise): self
    {
        if ($this->datesPrises->removeElement($datePrise)) {
            if ($datePrise->getPilule() === $this) {
                $datePrise->setPilule(null);
            }
        }

        return $this;
    }


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
        return $this->proprietaire;
    }

    public function setProprietaire(?Utilisateur $utilisateur): static
    {
        $this->proprietaire = $utilisateur;

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

    public function getDateDerniereReprise(): ?\DateTimeInterface
    {
        return $this->dateDerniereReprise;
    }

    public function setDateDerniereReprise(?\DateTimeInterface $dateDerniereReprise): static
    {
        $this->dateDerniereReprise = $dateDerniereReprise;

        return $this;
    }

    public function getDateProchainePause(): string
    {
        if ($this->dateDerniereReprise === null || $this->nbPilulesPlaquette === null) {
            return 'N/A';
        }

        $dateDerniereReprise = $this->dateDerniereReprise->format('Y-m-d');
        $nbPilulesPlaquette = $this->nbPilulesPlaquette;

        return date('d/m', strtotime($dateDerniereReprise . ' + ' . $nbPilulesPlaquette . ' days'));

    }
}
