<?php

namespace App\Data\FakeStore\Responses;

readonly class DeleteResponseData
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $details = [],
    ) {}

    public static function fromArray(array $details, string $resource): self
    {
        return new self(
            success: true,
            message: "Operacion de eliminacion de {$resource} completada.",
            details: $details,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
