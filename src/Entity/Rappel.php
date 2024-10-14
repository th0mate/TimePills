<?php

namespace App\Entity;

use App\Repository\RappelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RappelRepository::class)]
class Rappel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $adresseMail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureDerniereNotif = null;

    #[ORM\Column]
    private ?int $idPilule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseMail(): ?string
    {
        return $this->adresseMail;
    }

    public function setAdresseMail(string $adresseMail): static
    {
        $this->adresseMail = $adresseMail;

        return $this;
    }

    public function getHeureDerniereNotif(): ?\DateTimeInterface
    {
        return $this->heureDerniereNotif;
    }

    public function setHeureDerniereNotif(\DateTimeInterface $heureDerniereNotif): static
    {
        $this->heureDerniereNotif = $heureDerniereNotif;

        return $this;
    }

    public function getIdPilule(): ?int
    {
        return $this->idPilule;
    }

    public function setIdPilule(int $idPilule): static
    {
        $this->idPilule = $idPilule;

        return $this;
    }
}
