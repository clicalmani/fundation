<?php
namespace Clicalmani\Fundation\Routing;

use Clicalmani\Fundation\Support\Facades\Facade;

/**
 * @method static string[] all()
 * @method static bool isApi()
 * @method static string getClientVerb()
 * @method static string current()
 * @method static \Clicalmani\Routing\Group|null group(mixed ...$parameters)
 * @method static void pattern(string $param, string $pattern)
 * @method static \Clicalmani\Routing\Validator|\Clicalmani\Routing\Group register(string $method, string $route, mixed $callback, ?bool $bind = true)
 * @method static \Clicalmani\Routing\Group controller(string $class)
 * @method static \Clicalmani\Routing\Validator|\Clicalmani\Routing\Group get(string $route, mixed $action = null)
 * @method static \Clicalmani\Routing\Resource apiResource(mixed $resource, ?string $controller = null, ?array $actions = [])
 */
class Route extends Facade
{}
