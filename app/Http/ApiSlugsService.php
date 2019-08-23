<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Class ApiSlugsService
 * @package App\Http
 */
class ApiSlugsService
{
    protected $defaultAction = '';

    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $slugs
     * @return bool
     */
    public function validateSlugs(string $slugs): bool
    {
        $slugs = $this->prepareSlugs($slugs);
        return count($slugs) >= 2;
    }

    /**
     * @param string $slugs
     * @return string
     */
    public function getApiVersionBySlugs(string $slugs): string
    {
        $slugs = $this->prepareSlugs($slugs);
        $ver = Arr::get($slugs, 0);
        return strtolower($ver);
    }

    /**
     * @param string $slugs
     * @return string
     */
    public function getActionNameBySlugs(string $slugs): string
    {
        $slugs = $this->prepareSlugs($slugs);

        $action = $this->defaultAction;
        if (count($slugs) > 2) {
            $action = Arr::last($slugs);
            $action = $this->slugToCamelCase($action);
        }

        $actionPrefix = strtolower($this->request->method());
        return $actionPrefix . $action;
    }

    /**
     * @param string $slugs
     * @return string
     */
    public function getControllerNameBySlugs(string $slugs): string
    {
        $version = $this->getApiVersionBySlugs($slugs);

        $slugs = $this->prepareSlugs($slugs);

        if (count($slugs) > 2) {
            $controllerSlugs = array_slice($slugs, 1, -1);
        } else {
            $controllerSlugs = array_slice($slugs, 1);
        }

        $controllerSlugs = array_map(function ($slug) {
            return $this->slugToCamelCase($slug);
        }, $controllerSlugs);

        array_unshift($controllerSlugs, $version);

        $controller = implode('\\', $controllerSlugs);

        $controller = 'App\\Http\\Controllers\\' . $controller;

        return $controller;
    }

    /**
     * @param string $slugs
     * @return array
     */
    protected function prepareSlugs(string $slugs): array
    {
        return explode('/', trim($slugs, '/'));
    }

    protected function slugToCamelCase(string $slug)
    {
        $slug = ucfirst(strtolower($slug));
        $slug = preg_replace_callback('/_.{1}/', function ($val) {
            return trim(strtoupper($val[0]), '_');
        }, $slug);
        return $slug;
    }
}
