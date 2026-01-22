<?php

namespace App\Entity;

use App\Repository\CarRepository;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'immatriculation est obligatoire")]
    #[Assert\Regex(
        pattern: "/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/",
        message: "Format invalide. Exemple attendu : AA-123-AA"
    )]
    private string $immatriculation;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La marque est obligatoire")]
    private string $marque;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le modèle est obligatoire")]
    private string $modele;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "L'année doit être positive")]
    private ?int $annee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $couleur = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
        choices: [
            'pris_en_charge',
            'diagnostic',
            'attente_pieces',
            'en_reparation',
            'pret',
            'livre'
        ],
        message: "État invalide"
    )]
    private string $etat;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date d'entrée est obligatoire")]
    private \DateTimeInterface $dateEntree;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateSortie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

   
     // Relation ManyToOne :
    
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    #[ORM\ManyToOne(inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateEntree(): ?\DateTime
    {
        return $this->dateEntree;
    }

    public function setDateEntree(\DateTime $dateEntree): static
    {
        $this->dateEntree = $dateEntree;

        return $this;
    }

    public function getDateSortie(): ?\DateTime
    {
        return $this->dateSortie;
    }

    public function setDateSortie(?\DateTime $dateSortie): static
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;

        return $this;
    }
     public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
    
    
    // NotBlank garantit la présence de la donnée, Regex garantit son format.

}
