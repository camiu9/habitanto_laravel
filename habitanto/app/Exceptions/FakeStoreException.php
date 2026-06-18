<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class FakeStoreException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 502,
        public readonly array $context = [],
    ) {
        parent::__construct($message, $status);
    }

    public static function connection(string $resource, Throwable $previous, array $context = []): self
    {
        $previousMessage = $previous->getMessage();

        if (str_contains($previousMessage, 'cURL error 28')) {
            return self::timeout($resource, $previous, $context);
        }

        return new self(
            message: "No fue posible conectar con Fake Store API para {$resource}.",
            status: 503,
            context: array_merge($context, ['previous' => $previousMessage]),
        );
    }

    public static function upstream(string $resource, int $status, array $context = []): self
    {
        return new self(
            message: match ($status) {
                400 => "La solicitud enviada a Fake Store API para {$resource} no es valida.",
                401 => "Fake Store API rechazo la autenticacion para {$resource}.",
                403 => "Fake Store API no autorizo el acceso a {$resource}.",
                404 => "Fake Store API no encontro el recurso solicitado para {$resource}.",
                422 => "Fake Store API no pudo procesar los datos enviados para {$resource}.",
                default => "Fake Store API devolvio un error al procesar {$resource}.",
            },
            status: $status >= 400 ? $status : 502,
            context: $context,
        );
    }

    public static function invalidResponse(string $resource, array $context = []): self
    {
        return new self(
            message: "La respuesta de Fake Store API para {$resource} no tiene el formato esperado.",
            status: 502,
            context: $context,
        );
    }

    public static function timeout(string $resource, Throwable $previous, array $context = []): self
    {
        return new self(
            message: "Fake Store API tardo demasiado en responder para {$resource}.",
            status: 504,
            context: array_merge($context, ['previous' => $previous->getMessage()]),
        );
    }

    public static function unexpected(string $resource, Throwable $previous, array $context = []): self
    {
        return new self(
            message: "Ocurrio un error interno al procesar {$resource}.",
            status: 500,
            context: array_merge($context, ['previous' => $previous->getMessage()]),
        );
    }
}
