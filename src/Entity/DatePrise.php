<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class DatePrise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups('pilule:read')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $datePrise = null;

    #[ORM\ManyToOne(targetEntity: Pilule::class, inversedBy: 'datesPrises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pilule $pilule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrise(): ?\DateTimeInterface
    {
        return $this->datePrise;
    }

    public function setDatePrise(\DateTimeInterface $datePrise): self
    {
        $this->datePrise = $datePrise;

        return $this;
    }

    public function getPilule(): ?Pilule
    {
        return $this->pilule;
    }

    public function setPilule(?Pilule $pilule): self
    {
        $this->pilule = $pilule;

        return $this;
    }
}