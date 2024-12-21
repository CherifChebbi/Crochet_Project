<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;


require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Vérifiez si la classe Kernel existe
if (!class_exists(Kernel::class)) {
    throw new RuntimeException('Kernel class not found');
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};