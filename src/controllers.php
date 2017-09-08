<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Controller\ChapterController;
use Controller\AdminController;
use Controller\AuthController;
use Controller\StaticController;

//Request::setTrustedProxies(array('127.0.0.1'));
$app->mount('/chapter', new ChapterController());
$app->mount('/admin', new AdminController());
$app->mount('/auth', new AuthController());
$app->mount('/static', new StaticController());




$app->get('/', function () use ($app) {
    return $app->redirect($app['url_generator']->generate('homepage'));
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});