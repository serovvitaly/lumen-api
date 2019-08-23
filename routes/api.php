<?php
/**
 * Application Routes
 *
 * Here is where you can register all of the routes for an application.
 * It is a breeze. Simply tell Lumen the URIs it should respond to
 * and give it the Closure to call when that URI is requested.
 *
 * @var Router $router
 */

use App\Http\ApiSlugsService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Router;

$allowMethods = [
    'GET',
    'POST',
];
$router->addRoute($allowMethods, '{slugs:.*}', function ($slugs, Request $request) use ($router)
{
    $apiSlugsService = new ApiSlugsService($request);

    if (!$apiSlugsService->validateSlugs($slugs)) {
        abort(405, 'Method Not Allowed');
    }

    $actionName = $apiSlugsService->getActionNameBySlugs($slugs);
    $controllerName = $apiSlugsService->getControllerNameBySlugs($slugs);

    $result = null;
    if (method_exists($controllerName, $actionName)) {
        $result = $router->app->make($controllerName)->$actionName();
    } else {
        abort(405, 'Method Not Allowed');
    }

    return [
        'ver' => $apiSlugsService->getApiVersionBySlugs($slugs),
        'result' => $result,
    ];
});
