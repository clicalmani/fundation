<?php
namespace Clicalmani\Fundation\Logic\Internal;

use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Http\Requests\HttpRequest;
use Clicalmani\Fundation\Exceptions\ModelNotFoundException;
use Clicalmani\Database\Factory\Models\Model;
use Clicalmani\Fundation\Http\Requests\RequestReflection;
use Clicalmani\Fundation\Providers\RouteServiceProvider;
use Clicalmani\Fundation\Routing\Exceptions\RouteNotFoundException;
use Clicalmani\Fundation\Routing\Route;
use Clicalmani\Fundation\Test\Controllers\TestController;
use Clicalmani\Fundation\Validation\AsValidator;
use Clicalmani\Routing\Cache;

/**
 * |------------------------------------------------------------------
 * | Init Service Providers
 * |------------------------------------------------------------------
 * 
 */

 $root_path = dirname( __DIR__, 6 );

 \Clicalmani\Fundation\Providers\ServiceProvider::init(
	 require $root_path . '/config/app.php',
	 require $root_path . '/bootstrap/kernel.php',
	 require $root_path . '/app/Http/kernel.php'
 );

/**
 * RequestController class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class RequestController extends HttpRequest
{
	/**
	 * Current request controller
	 * 
	 * @var mixed
	 */
	private $controller;

	/**
	 * Current route
	 * 
	 * @var \Clicalmani\Routing\Route
	 */
	private $route;

	/**
	 * Render request response
	 * 
	 * @return never
	 */
	public function render() : never
	{
		$request = new Request;

		/**
		 * Check CSRF protection
		 * 
		 * |----------------------------------------------------------
		 * | Note !!!
		 * |----------------------------------------------------------
		 * CSRF protection is only based csrf-token request parameter. No CSRF header will be expected
		 * because we asume ajax requests will be made through REST API.
		 */
		if (Route::getClientVerb() !== 'get' AND FALSE == $request->checkCSRFToken()) {
			response()->status(403, 'FORBIDEN', '403 Forbiden');

			EXIT;
		}

		$response = $this->getResponse($request);
		
		// Run after navigation hook
		if ($hook = $this->route->afterHook()) $response = $hook($response);

		// Fire TPS
		RouteServiceProvider::fireTPS($response, 1);
		
		die($response);
	}

	/**
	 * Resolve request controller
	 * 
	 * @return mixed
	 */
    private function getController() : mixed
	{
		if ( isset( $this->controller ) ) {
			return $this->controller;
		}
		
		$request = new Request([]);
		
		if ($route = (new \Clicalmani\Routing\Builder)->build()) {
			
			$this->route      = $route;

			// Redirect
			if ($route->redirect) $this->redirect();

			$this->controller = $route->action;
			
			Cache::currentRoute($route);
			
			if ( $response_code = $route->isAuthorized($request) AND 200 !== $response_code) {
				
				switch($response_code) {
					case 401: $this->sendStatus($response_code, 'UNAUTHORIZED_REQUEST_ERROR', 'Request Unauthorized'); break;
					case 403: $this->sendStatus($response_code, 'FORBIDEN', 'Request Forbiden'); break;
					case 404: $this->sendStatus($response_code, 'NOT FOUND', 'Not Found'); break;
					default: $this->sendStatus($response_code, 'UNKNOW', 'Unknow'); break;
				}
				
				exit;
			}
			
			return $this->controller;
		}
		
		throw new RouteNotFoundException( current_route() );
    }
	
	/**
	 * Get request response
	 * 
	 * @param \Clicalmani\Fundation\Http\Requests\Request
	 * @return mixed
	 */
	private function getResponse(Request $request) : mixed
	{
		$controller = $this->getController();
		
		/**
		 * Checks for controller
		 */
		if (is_array($controller) AND !empty($controller)) {
			$class = $controller[0];
			$obj   = new $class;                                             // An instance of the controller
			
			if ( @ isset($controller[1]) ) {

				if ( ! method_exists($obj, $controller[1]) ) {
					response()->status(500, 'INTERNAL_SERVER_ERROR', 'Method ' . $controller[1] . ' does not exist on class ' . $controller[0]);		// Forbiden
					exit;
				}
				
				return $this->invokeControllerMethod($class, $controller[1]);
			}

			return $this->invokeControllerMethod($class);

		} elseif( is_string($controller) ) {

			return $this->invokeControllerMethod($controller);			  // Controller with magic method invoke

		} elseif ($controller instanceof \Closure) {                      // Otherwise fallback to closure function
			                                                              // whith a default Request object
			return $controller(...($this->getParameters($request)));
		}

		throw new RouteNotFoundException(current_route());
	}

	/**
	 * Run route action
	 * 
	 * @param mixed $controllerClass
	 * @param mixed $method
	 * @return mixed
	 */
	public function invokeControllerMethod($controllerClass, $method = '__invoke') : mixed
	{
		$request = new Request;							  // Fallback to default request
		$reflect = new RequestReflection($controllerClass, $method);
		
		/**
		 * Validate request
		 */
		if ( $requestClass = $reflect->getParamTypeAt(0) ) {
			
			// Model binding request
			if ( $this->isResourceBound($requestClass) ) {
				try {
					return $this->bindResource($requestClass, $controllerClass, $method);
				} catch(ModelNotFoundException $e) {
					
					if ( $callback = $this->route->missing() ) {
						return $callback();
					}

					return response()->status(404, 'NOT_FOUND', $e->getMessage());		// Not Found
				}
			}
			
			/** @var \Clicalmani\Fundation\Http\Requests\Request */
			$request = new $requestClass;
			$this->validateRequest(new $request);
		}
		
		$params_types = $reflect->getParamsTypes();
		$params_values = $this->getParameters($request);

		array_shift($params_types);
		
		$this->setRequestParameterTypes($params_types, $params_values, $method, $controllerClass);
		Request::currentRequest($request); // Current request

		if ($attribute = (new \ReflectionMethod($controllerClass, $method))->getAttributes(AsValidator::class)) {
            $request->merge($attribute[0]->newInstance()->args);
        }
		
		if ($method !== '__invoke') return (new $controllerClass)->{$method}($request, ...$params_values);

        return (new $controllerClass)($request, ...$params_values);
	}

	/**
	 * Validate request
	 * 
	 * @param \Clicalmani\Fundation\Http\Requests\Request
	 * @return mixed
	 */
	private function validateRequest(Request $request) : mixed
	{
		if (method_exists($request, 'authorize')) {
			if (false == $request->authorize()) {
				return response()->status(403, 'FORBIDEN', 'Unauthorized Request');		// Forbiden
			}
		}

		if (method_exists($request, 'prepareForValidation')) {
			$request->prepareForValidation();                    // Call prepareForValidation method
		}
		
		if (method_exists($request, 'signatures')) {
			$request->signatures();                             // Set parameters signatures
		}

		return null;
	}

	/**
	 * Set parameters types
	 * 
	 * @param string[] $types
	 * @param string[] $values
	 * @param string $method Controller method
	 * @param string $controller Controller class
	 * @return void
	 */
	private function setRequestParameterTypes(array $types, array &$values, string $method, string $controller) : void
	{
		$tmp = [];
		foreach ($types as $name => $type) {
			if (in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object'])) {
				$tmp[$name] = @ $values[$name];
				settype($tmp[$name], $type);
			} elseif ($type) {
				$obj = new $type;

				if (is_subclass_of($obj, \Clicalmani\Fundation\Http\Requests\Request::class)) {
					$this->validateRequest($obj);
					Request::currentRequest($obj); // Current request

					if ($attribute = (new \ReflectionMethod($controller, $method))->getAttributes(AsValidator::class)) {
						$obj->merge($attribute[0]->newInstance()->args);
					}
				}

				$tmp[$name] = $obj;
			} else $tmp[$name] = @ $values[$name];
		}

		$values = $tmp;
	}

	/**
	 * Gather request parameters
	 * 
	 * @param \Clicalmani\Fundation\Http\Requests\Request
	 * @return array
	 */
	private function getParameters(Request $request) : array
    {
		if ( inConsoleMode() ) return $request->all();
		
        preg_match_all('/:[^\/]+/', (string) $this->route, $mathes);

        $parameters = [];
        
        if ( count($mathes) ) {

            $mathes = $mathes[0];
            
            foreach ($mathes as $name) {
                $name = substr($name, 1);    				      // Remove starting two dots (:)
                
                if (preg_match('/@/', $name)) {
                    $name = substr($name, 0, strpos($name, '@')); // Remove validation part
                }
                
                if ($request->{$name}) {
                    $parameters[$name] = $request->{$name};
                }
            }
        }

        return $parameters;
    }

	/**
	 * Is resource bind
	 * 
	 * @param mixed $resource
	 * @return bool
	 */
	private function isResourceBound(mixed $resource) : bool
	{
		return is_subclass_of($resource, \Clicalmani\Database\Factory\Models\Model::class);
	}

	/**
	 * Bind a model resource
	 * 
	 * @param mixed $resource
	 * @param mixed $controller
	 * @param mixed $method
	 * @return mixed
	 */
	private function bindResource(mixed $resource, mixed $controller, mixed $method) : mixed
	{
		$request = new Request;
		$obj     = new $resource;
		$reflect = new RequestReflection($controller, $method);
		
		$params_types = $reflect->getParamsTypes();
		$params_values = $this->getParameters($request);
		
		array_shift($params_types);

		$this->setRequestParameterTypes($params_types, $params_values, $method, $controller);
		Request::currentRequest($request); // Current request

		if ($attribute = (new \ReflectionMethod($controller, $method))->getAttributes(AsValidator::class)) {
            $request->merge($attribute[0]->newInstance()->args);
        }
		
		if ( in_array($method, ['create', 'show', 'update', 'destroy']) ) {

			// Request parameters
			$parameters = explode(',', (string) $request->id);
			
			if ( count($parameters) ) {
				if ( count($parameters) === 1 ) $parameters = $parameters[0];	// Single primary key
				
				/** @var \Clicalmani\Database\Factory\Models\Model */
				$model = new $resource($parameters);
				
				/**
				 * Bind resources
				 */
				$this->bindRoutines($model);
				
				$collection = $model->get();

				if ( $collection->isEmpty() ) throw new ModelNotFoundException($resource);
				
				return (new $controller)->{$method}($model, ...$params_values);

			} else throw new ModelNotFoundException($resource);
		}

		/** @var \Clicalmani\Database\Factory\Models\Model */
		$model = new $resource;

		/**
		 * Bind resources
		 */
		$this->bindRoutines($model);
		
		return (new $controller)->{$method}($model, ...$params_values);
	}

	/**
	 * Bind resource routines
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function bindRoutines(Model $model) : void
	{
		/**
		 * Select distinct
		 */
		$this->getResourceDistinct($model);

		/**
		 * Insert ignore
		 */
		$this->createResourceIgnore($model);

		/**
		 * Delete multiple
		 */
		$this->resourceDeleteFrom($model);

		/**
		 * Pagination
		 */
		$this->resourceCalcRows($model);

		/**
		 * Limit rows
		 */
		$this->resourceLimit($model);

		/**
		 * Row order by
		 */
		$this->resourceOrderBy($model);
	}

	/**
	 * Distinct rows
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function getResourceDistinct(Model $obj) : void
	{
		if ( $distinct = $this->route->distinctResult() ) {
			$obj->distinct($distinct);
		}
	}

	/**
	 * Ignore duplicates
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function createResourceIgnore(Model $obj) : void
	{
		if ( $ignore = $this->route->ignoreKeyWarning() ) {
			$obj->ignore($ignore);
		}
	}

	/**
	 * Delete from
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function resourceDeleteFrom(Model $obj) : void
	{
		if ( $from = $this->route->deleteFrom() ) {
			$obj->from($from);
		}
	}

	/**
	 * Calc rows
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function resourceCalcRows(Model $obj) : void
	{
		if ( $enable = $this->route->calcFoundRows() ) {
			$obj->calcFoundRows($enable);
		}
	}

	/**
	 * Limit rows
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function resourceLimit(Model $obj) : void
	{
		if ( $arr = $this->route->limitResult() ) {
			$obj->limit($arr['offset'], $arr['count']);
		}
	}

	/**
	 * Order by
	 * 
	 * @param \Clicalmani\Database\Factory\Models\Model $obj
	 * @return void
	 */
	private function resourceOrderBy(Model $obj) : void
	{
		if ( $order_by = $this->route->orderResultBy() ) {
			$obj->orderBy($order_by);
		}
	}

	/**
	 * Controller test
	 * 
	 * @param string $action Test action
	 * @return \Clicalmani\Fundation\Test\Controllers\TestController
	 */
	public function test(string $action)
	{
		return with( new TestController )->new($action);
	}

	private function redirect()
	{
		/** @var int */
		$redirect_code = $this->route->redirect;

		switch($redirect_code) {
			case 302: $this->sendStatus($redirect_code, 'FOUND', 'Temporary Unavailable'); break;
			case 301: $this->sendStatus($redirect_code, 'PAGE_MOVED_PERMANENTLY', 'Page Moved Permenently'); break;
			case 308: $this->sendStatus($redirect_code, 'PERMANENT_REDIRECT', 'Permanent Redirect'); break;
			case 303: $this->sendStatus($redirect_code, 'SEE_OTHER', 'Redirect'); break;
			case 307: $this->sendStatus($redirect_code, 'PERMANENTLY_REDIRECT', 'Temporary Unavailable'); break;
			case 300: $this->sendStatus($redirect_code, 'MULTIPLE_CHOICES', 'Multiple Choices'); break;
			case 304: $this->sendStatus($redirect_code, 'NOT_MODIFIED', 'Not Modified'); break;
			default: $this->sendStatus($redirect_code, 'UNKNOW', 'Unknow'); break;
		}
		
		exit;
	}

	private function sendStatus(int $code, string $status_code, string $message)
	{
		if (Route::isApi()) response()->status($code, $status_code, $message);
		else response()->send($code);

		exit;
	}
}
