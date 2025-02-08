<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $request = Request::createFromGlobals();
    $response = new Response();

    // Serve the index.html file directly
    if ($request->getPathInfo() === '/') {
        $response->setContent(file_get_contents(__DIR__.'/index.html'));
        $response->send();
        exit;
    }
    
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};