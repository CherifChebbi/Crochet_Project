<?php
namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $customerName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $customerAddress = null;

    #[ORM\Column(type: 'string', length: 15)]
    private ?string $customerPhone = null;

    #[ORM\Column(type: 'float')]
    private ?float $totalAmount = null;

    // Stocker les IDs des produits sous forme de chaîne (séparée par des tirets)
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $productIds = '';

    public function __construct()
    {
        // Initialiser la chaîne vide
        $this->productIds = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customerAddress;
    }

    public function setCustomerAddress(string $customerAddress): self
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Récupérer les IDs des produits sous forme de tableau
     */
    public function getProductIds(): array
    {
        return explode('-', $this->productIds); // Convertir la chaîne en tableau
    }

    /**
     * Définir les IDs des produits sous forme de chaîne (séparée par des tirets)
     */
    public function setProductIds(array $productIds): self
    {
        $this->productIds = implode('-', $productIds); // Convertir le tableau en chaîne

        return $this;
    }

    // Méthodes pour ajouter ou supprimer des produits
    public function addProductId(int $productId): self
    {
        $productIds = $this->getProductIds();
        if (!in_array($productId, $productIds)) {
            $productIds[] = $productId;
        }
        $this->setProductIds($productIds);

        return $this;
    }

    public function removeProductId(int $productId): self
    {
        $productIds = $this->getProductIds();
        $productIds = array_filter($productIds, fn($id) => $id != $productId);
        $this->setProductIds($productIds);

        return $this;
    }
}