<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $size = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $availability = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Media::class, cascade: ['persist', 'remove'])]
    private Collection $media;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $nbr_media = 0;  // New field to store the media count

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $productDate = null;  // Date d ajout du product

    public function __construct()
    {
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isAvailability(): ?bool
    {
        return $this->availability;
    }

    public function setAvailability(?bool $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
    public function getNbrMedia(): int
    {
        return $this->nbr_media;
    }

    public function setNbrMedia(int $nbr_media): static
    {
        $this->nbr_media = $nbr_media;

        return $this;
    }
    // Getter et setter pour productDate
    public function getProductDate(): ?\DateTimeInterface
    {
        return $this->productDate;
    }

    public function setProductDate(\DateTimeInterface $productDate): self
    {
        $this->productDate = $productDate;
        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    
    public function addMedia(Media $media): static
    {
        if (!$this->media->contains($media)) {
            $this->media[] = $media;
            $media->setProduct($this);

            // Update media count when adding a new media
            $this->nbr_media++;
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->media->removeElement($media)) {
            // Set the owning side to null (unless already changed)
            if ($media->getProduct() === $this) {
                $media->setProduct(null);
            // Update media count when removing a media
            $this->nbr_media--;
            }

            
        }

        return $this;
    }
}
