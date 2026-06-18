<?php

namespace App\Services\FakeStore;

use App\Data\FakeStore\Requests\CartRequestData;
use App\Data\FakeStore\Responses\CartResponseData;
use App\Data\FakeStore\Responses\DeleteResponseData;
use App\Repositories\FakeStore\CartRepository;

class CartService
{
    public function __construct(
        private readonly CartRepository $repository,
    ) {}

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function create(CartRequestData $request): CartResponseData
    {
        return $this->repository->create($request);
    }

    public function getById(int $id): CartResponseData
    {
        return $this->repository->find($id);
    }

    public function update(int $id, CartRequestData $request): CartResponseData
    {
        return $this->repository->update($id, $request);
    }

    public function delete(int $id): DeleteResponseData
    {
        return DeleteResponseData::fromArray($this->repository->delete($id), 'cart');
    }
}
