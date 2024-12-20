<?php

namespace App\Ecommerce\Kernel;

use App\Ecommerce\Controller\API\AuthMiddleware;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

class Kernel
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function run($interface) {
        $app = AppFactory::create(null, $this->container);

        // Middleware CORS
        $app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            $origin = $request->getHeaderLine('Origin');

            return $response
                ->withHeader('Access-Control-Allow-Origin', $origin ?: '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        });

        // Handle OPTIONS requests for all routes
        $app->options('/{routes:.+}', function ($request, $response, $args) {
            return $response->withStatus(200);
        });

        // Error Handling Middleware
        $app->addErrorMiddleware(true, true, true);

        // Define routes
        $app->get('/', \App\Ecommerce\Controller\Front\HomePage\IndexAction::class);
        $app->get("/product/{id}", \App\Ecommerce\Controller\API\Product\ProductByIdAction::class);
        $app->get("/products", \App\Ecommerce\Controller\Front\Products::class);
        $app->get("/category", \App\Ecommerce\Controller\API\Listing\CategoryListingAction::class);
        $app->get("/productsByCategory/{id}", \App\Ecommerce\Controller\Front\ProductsByCategory::class);
        

        $app->get("/admin/product/{id}", \App\Ecommerce\Controller\Admin\Product\ViewAction::class);

        $app->group("/api", function (RouteCollectorProxy $group) {
            $group->get("/products", \App\Ecommerce\Controller\API\Listing\ProductListingAction::class);
            $group->get("/product/{id}", \App\Ecommerce\Controller\API\Product\ProductByIdAction::class);
            $group->get("/category", \App\Ecommerce\Controller\API\Listing\CategoryListingAction::class);

            $group->post("/contact", \App\Ecommerce\Controller\API\Contact\SendEmail::class);
        })->add(AuthMiddleware::class);

        $app->run();
    }
}
