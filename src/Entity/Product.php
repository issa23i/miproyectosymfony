<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Familia;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer")
     */
    private $precio;

    /**
     * @ORM\ManyToOne(targetEntity=Familia::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $familia;

    /**
     * @ORM\OneToMany(targetEntity=PedidosProductos::class, mappedBy="producto", orphanRemoval=true)
     */
    private $pedidosProductos;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $cod;

    public function __construct()
    {
        $this->pedidosProductos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(int $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getFamilia(): ?Familia
    {
        return $this->familia;
    }

    public function setFamilia(?Familia $familia): self
    {
        $this->familia = $familia;

        return $this;
    }

    /**
     * @return Collection<int, PedidosProductos>
     */
    public function getPedidosProductos(): Collection
    {
        return $this->pedidosProductos;
    }

    public function addPedidosProducto(PedidosProductos $pedidosProducto): self
    {
        if (!$this->pedidosProductos->contains($pedidosProducto)) {
            $this->pedidosProductos[] = $pedidosProducto;
            $pedidosProducto->setProducto($this);
        }

        return $this;
    }

    public function removePedidosProducto(PedidosProductos $pedidosProducto): self
    {
        if ($this->pedidosProductos->removeElement($pedidosProducto)) {
            // set the owning side to null (unless already changed)
            if ($pedidosProducto->getProducto() === $this) {
                $pedidosProducto->setProducto(null);
            }
        }

        return $this;
    }

    public function getCod(): ?string
    {
        return $this->cod;
    }

    public function setCod(string $cod): self
    {
        $this->cod = $cod;

        return $this;
    }
    
}
