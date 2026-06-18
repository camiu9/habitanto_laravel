<?php

namespace App\Services\FakeStore;

use App\Data\FakeStore\Requests\ProductRequestData;
use App\Data\FakeStore\Responses\DeleteResponseData;
use App\Data\FakeStore\Responses\ProductResponseData;
use App\Repositories\FakeStore\ProductRepository;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function create(ProductRequestData $request): ProductResponseData
    {
        return $this->repository->create($request);
    }

    public function getById(int $id): ProductResponseData
    {
        return $this->repository->find($id);
    }

    public function update(int $id, ProductRequestData $request): ProductResponseData
    {
        return $this->repository->update($id, $request);
    }

    public function delete(int $id): DeleteResponseData
    {
        return DeleteResponseData::fromArray($this->repository->delete($id), 'product');
    }
}
