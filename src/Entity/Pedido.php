<?php

namespace App\Entity;

use App\Repository\PedidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PedidoRepository::class)
 */
class Pedido
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @ORM\Column(type="float")
     */
    private $coste;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="pedidos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\OneToMany(targetEntity=PedidosProductos::class, mappedBy="pedido", orphanRemoval=true)
     */
    private $pedidosProductos;

    public function __construct()
    {
        $this->pedidosProductos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getCoste(): ?float
    {
        return $this->coste;
    }

    public function setCoste(float $coste): self
    {
        $this->coste = $coste;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

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
            $pedidosProducto->setPedido($this);
        }

        return $this;
    }

    public function removePedidosProducto(PedidosProductos $pedidosProducto): self
    {
        if ($this->pedidosProductos->removeElement($pedidosProducto)) {
            // set the owning side to null (unless already changed)
            if ($pedidosProducto->getPedido() === $this) {
                $pedidosProducto->setPedido(null);
            }
        }

        return $this;
    }
}
